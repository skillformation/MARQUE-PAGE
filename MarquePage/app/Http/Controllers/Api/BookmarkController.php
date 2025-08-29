<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookmarkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Book $book): JsonResponse
    {
        $this->authorize('view', $book);

        $bookmarks = $book->bookmarks()->orderBy('page_number')->get();

        return response()->json([
            'success' => true,
            'data' => $bookmarks
        ]);
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $validator = Validator::make($request->all(), [
            'page_number' => 'required|integer|min:1',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'note' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bookmarkData = $validator->validated();
        $bookmarkData['book_id'] = $book->id;

        $bookmark = Bookmark::create($bookmarkData);

        return response()->json([
            'success' => true,
            'message' => 'Bookmark created successfully',
            'data' => $bookmark
        ], 201);
    }

    public function show(Book $book, Bookmark $bookmark): JsonResponse
    {
        $this->authorize('view', $book);
        
        if ($bookmark->book_id !== $book->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmark not found for this book'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bookmark
        ]);
    }

    public function update(Request $request, Book $book, Bookmark $bookmark): JsonResponse
    {
        $this->authorize('update', $book);

        if ($bookmark->book_id !== $book->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmark not found for this book'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'page_number' => 'sometimes|required|integer|min:1',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'note' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bookmark->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Bookmark updated successfully',
            'data' => $bookmark
        ]);
    }

    public function destroy(Book $book, Bookmark $bookmark): JsonResponse
    {
        $this->authorize('update', $book);

        if ($bookmark->book_id !== $book->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmark not found for this book'
            ], 404);
        }

        $bookmark->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bookmark deleted successfully'
        ]);
    }
}