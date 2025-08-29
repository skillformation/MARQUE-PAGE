<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Auth::user()->books();

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

        if ($request->has('author')) {
            $query->where('author', 'like', '%' . $request->get('author') . '%');
        }

        $books = $query->with(['bookmarks', 'quotes'])
                      ->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:17',
            'description' => 'nullable|string',
            'genre' => 'nullable|string|max:100',
            'total_pages' => 'nullable|integer|min:1',
            'current_page' => 'nullable|integer|min:0',
            'status' => 'nullable|in:to_read,reading,completed',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bookData = $validator->validated();
        $bookData['user_id'] = Auth::id();

        if ($request->hasFile('cover_image')) {
            $bookData['cover_image'] = $this->uploadAndProcessImage($request->file('cover_image'));
        }

        if ($bookData['status'] === 'reading' && !isset($bookData['started_at'])) {
            $bookData['started_at'] = now();
        }

        if ($bookData['status'] === 'completed' && !isset($bookData['completed_at'])) {
            $bookData['completed_at'] = now();
        }

        $book = Book::create($bookData);
        $book->load(['bookmarks', 'quotes']);

        return response()->json([
            'success' => true,
            'message' => 'Book created successfully',
            'data' => $book
        ], 201);
    }

    public function show(Book $book): JsonResponse
    {
        $this->authorize('view', $book);
        
        $book->load(['bookmarks', 'quotes']);

        return response()->json([
            'success' => true,
            'data' => $book
        ]);
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'isbn' => 'nullable|string|max:17',
            'description' => 'nullable|string',
            'genre' => 'nullable|string|max:100',
            'total_pages' => 'nullable|integer|min:1',
            'current_page' => 'nullable|integer|min:0',
            'status' => 'nullable|in:to_read,reading,completed',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bookData = $validator->validated();

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $bookData['cover_image'] = $this->uploadAndProcessImage($request->file('cover_image'));
        }

        if (isset($bookData['status'])) {
            if ($bookData['status'] === 'reading' && $book->status !== 'reading' && !$book->started_at) {
                $bookData['started_at'] = now();
            }
            if ($bookData['status'] === 'completed' && $book->status !== 'completed' && !$book->completed_at) {
                $bookData['completed_at'] = now();
            }
        }

        $book->update($bookData);
        $book->load(['bookmarks', 'quotes']);

        return response()->json([
            'success' => true,
            'message' => 'Book updated successfully',
            'data' => $book
        ]);
    }

    public function destroy(Book $book): JsonResponse
    {
        $this->authorize('delete', $book);

        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book deleted successfully'
        ]);
    }

    public function updateProgress(Request $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $validator = Validator::make($request->all(), [
            'current_page' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $currentPage = $request->get('current_page');
        $updateData = ['current_page' => $currentPage];

        if ($book->status === 'to_read' && $currentPage > 0) {
            $updateData['status'] = 'reading';
            $updateData['started_at'] = $book->started_at ?? now();
        }

        if ($book->total_pages && $currentPage >= $book->total_pages && $book->status !== 'completed') {
            $updateData['status'] = 'completed';
            $updateData['completed_at'] = $book->completed_at ?? now();
        }

        $book->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Reading progress updated successfully',
            'data' => [
                'current_page' => $book->current_page,
                'progress_percentage' => $book->progress_percentage,
                'status' => $book->status
            ]
        ]);
    }

    private function uploadAndProcessImage($file): string
    {
        $manager = new ImageManager(new Driver());
        
        $image = $manager->read($file);
        $image = $image->resize(400, 600, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $filename = 'books/' . uniqid() . '.jpg';
        $path = storage_path('app/public/' . $filename);
        
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $image->save($path, 85);

        return $filename;
    }
}