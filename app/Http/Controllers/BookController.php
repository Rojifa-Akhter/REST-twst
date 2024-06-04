<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with(['author', 'publisher'])->get();

        $data = [
            'status' => 200,
            'books' => $books,
        ];

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author_id' => 'required|exists:authors,id',
            'publisher_id' => 'required|exists:publishers,id',
            'published_date' => 'required|date',
            'isbn' => 'required|string|max:13|unique:books,isbn',
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'errors' => $validator->errors(),
            ];
            return response()->json($data, 422);
        }

        $book = new Book();
        $book->title = $request->title;
        $book->author_id = $request->author_id;
        $book->publisher_id = $request->publisher_id;
        $book->published_date = $request->published_date;
        $book->isbn = $request->isbn;

        $book->save();

        $data = [
            'status' => 200,
            'message' => 'Data stored successfully',
            'book' => $book->load(['author', 'publisher']),
        ];

        return response()->json($data, 200);
    }

    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author_id' => 'required|exists:authors,id',
            'publisher_id' => 'required|exists:publishers,id',
            'published_date' => 'required|date',
            'isbn' => 'required|string|max:13|unique:books,isbn,' . $id,
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 422,
                'errors' => $validator->errors(),
            ];
            return response()->json($data, 422);
        }
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['status' => 404, 'message' => 'Book not found'], 404);
        }
        $book->title = $request->title;
        $book->author_id = $request->author_id;
        $book->publisher_id = $request->publisher_id;
        $book->published_date = $request->published_date;
        $book->isbn = $request->isbn;

        $book->save();

        $data = [
            'status' => 200,
            'message' => 'Data updated successfully',
            'book' => $book->load(['author', 'publisher']),
        ];

        return response()->json($data, 200);
    }

    public function delete($id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['status' => 404, 'message' => 'Book not found'], 404);
        }

        try {
            $book->delete();
            $data = [
                'status' => 200,
                'message' => 'Data deleted successfully',
            ];
            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error('Error deleting book: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Internal Server Error'], 500);
        }
    }

}
