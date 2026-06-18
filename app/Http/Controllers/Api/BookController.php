<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Http\Resources\Api\BookResource;

class BookController extends Controller
{

public function index(Request $request)
{
    $query = Book::with(['author', 'category']);

    if ($request->filled('category_name')) {
        $query->whereHas('category', function ($q) use ($request) {
            $q->where('name', $request->category_name);
        });
    }

    return BookResource::collection(
        $query->get()
    );
}
}
