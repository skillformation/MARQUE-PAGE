<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            // Récupérer l'utilisateur à partir du token simple
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
            }

            $query = Book::where('user_id', $user->id);

            // Filtres
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('genre', 'like', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->has('genre')) {
                $query->where('genre', $request->get('genre'));
            }

            $books = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $books
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Récupérer l'utilisateur à partir du token simple
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'isbn' => 'nullable|string|max:17',
                'genre' => 'nullable|string|max:100',
                'total_pages' => 'nullable|integer|min:1',
                'summary' => 'nullable|string|max:2000',
                'status' => 'nullable|in:to_read,reading,completed',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $bookData = $validator->validated();
            $bookData['user_id'] = $user->id;
            $bookData['current_page'] = 0;

            // Traitement de l'image de couverture
            if ($request->hasFile('cover_image')) {
                $bookData['cover_image'] = $this->uploadAndProcessImage($request->file('cover_image'));
            }

            // Gestion des dates selon le statut
            if (isset($bookData['status'])) {
                if ($bookData['status'] === 'reading') {
                    $bookData['started_at'] = now();
                } elseif ($bookData['status'] === 'completed') {
                    $bookData['started_at'] = $bookData['started_at'] ?? now();
                    $bookData['completed_at'] = now();
                }
            }

            $book = Book::create($bookData);

            return response()->json([
                'success' => true,
                'message' => 'Livre ajouté avec succès',
                'data' => $book
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
            }

            $book = Book::where('user_id', $user->id)->find($id);
            
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livre non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $book
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
            }

            $book = Book::where('user_id', $user->id)->find($id);
            
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livre non trouvé'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'author' => 'sometimes|required|string|max:255',
                'isbn' => 'nullable|string|max:17',
                'genre' => 'nullable|string|max:100',
                'total_pages' => 'nullable|integer|min:1',
                'current_page' => 'nullable|integer|min:0',
                'summary' => 'nullable|string|max:2000',
                'status' => 'nullable|in:to_read,reading,completed',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'rating' => 'nullable|numeric|min:0|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $bookData = $validator->validated();

            // Traitement de l'image de couverture
            if ($request->hasFile('cover_image')) {
                // Supprimer l'ancienne image
                if ($book->cover_image) {
                    Storage::disk('public')->delete($book->cover_image);
                }
                $bookData['cover_image'] = $this->uploadAndProcessImage($request->file('cover_image'));
            }

            // Gestion des dates selon le statut
            if (isset($bookData['status'])) {
                if ($bookData['status'] === 'reading' && $book->status !== 'reading' && !$book->started_at) {
                    $bookData['started_at'] = now();
                }
                if ($bookData['status'] === 'completed' && $book->status !== 'completed' && !$book->completed_at) {
                    $bookData['completed_at'] = now();
                }
            }

            $book->update($bookData);

            return response()->json([
                'success' => true,
                'message' => 'Livre mis à jour avec succès',
                'data' => $book
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
            }

            $book = Book::where('user_id', $user->id)->find($id);
            
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livre non trouvé'
                ], 404);
            }

            // Supprimer l'image de couverture
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }

            $book->delete();

            return response()->json([
                'success' => true,
                'message' => 'Livre supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProgress(Request $request, $id): JsonResponse
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
            }

            $book = Book::where('user_id', $user->id)->find($id);
            
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Livre non trouvé'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'current_page' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $currentPage = $request->get('current_page');
            $updateData = ['current_page' => $currentPage];

            // Auto-update du statut selon la progression
            if ($book->status === 'to_read' && $currentPage > 0) {
                $updateData['status'] = 'reading';
                $updateData['started_at'] = $book->started_at ?? now();
            }

            if ($book->total_pages && $currentPage >= $book->total_pages && $book->status !== 'completed') {
                $updateData['status'] = 'completed';
                $updateData['completed_at'] = now();
            }

            $book->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Progression mise à jour',
                'data' => [
                    'current_page' => $book->current_page,
                    'progress_percentage' => $book->progress_percentage,
                    'status' => $book->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    private function uploadAndProcessImage($file): string
    {
        try {
            $manager = new ImageManager(new Driver());
            
            $image = $manager->read($file);
            
            // Redimensionner en gardant le ratio (400x600 max)
            $image = $image->resize(400, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Créer un nom de fichier unique
            $filename = 'books/' . uniqid() . '_' . time() . '.jpg';
            $path = storage_path('app/public/' . $filename);
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            // Sauvegarder avec compression (85% qualité)
            $image->save($path, 85);

            return $filename;

        } catch (\Exception $e) {
            throw new \Exception('Erreur lors du traitement de l\'image: ' . $e->getMessage());
        }
    }

    private function getUserFromToken(Request $request): ?User
    {
        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        try {
            $decoded = base64_decode($token);
            $email = explode(':', $decoded)[0];
            return User::where('email', $email)->first();
        } catch (\Exception $e) {
            return null;
        }
    }
}