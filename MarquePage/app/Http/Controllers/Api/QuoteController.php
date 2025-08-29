<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Book $book): JsonResponse
    {
        $this->authorize('view', $book);

        $quotes = $book->quotes()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $quotes
        ]);
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000',
            'page_number' => 'nullable|integer|min:1',
            'context' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $quoteData = $validator->validated();
        $quoteData['book_id'] = $book->id;

        $quote = Quote::create($quoteData);

        return response()->json([
            'success' => true,
            'message' => 'Quote created successfully',
            'data' => $quote
        ], 201);
    }

    public function show(Book $book, Quote $quote): JsonResponse
    {
        $this->authorize('view', $book);
        
        if ($quote->book_id !== $book->id) {
            return response()->json([
                'success' => false,
                'message' => 'Quote not found for this book'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $quote
        ]);
    }

    public function update(Request $request, Book $book, Quote $quote): JsonResponse
    {
        $this->authorize('update', $book);

        if ($quote->book_id !== $book->id) {
            return response()->json([
                'success' => false,
                'message' => 'Quote not found for this book'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|required|string|max:2000',
            'page_number' => 'nullable|integer|min:1',
            'context' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $quote->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Quote updated successfully',
            'data' => $quote
        ]);
    }

    public function destroy(Book $book, Quote $quote): JsonResponse
    {
        $this->authorize('update', $book);

        if ($quote->book_id !== $book->id) {
            return response()->json([
                'success' => false,
                'message' => 'Quote not found for this book'
            ], 404);
        }

        $quote->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quote deleted successfully'
        ]);
    }
}