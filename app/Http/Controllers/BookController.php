<?php

namespace App\Http\Controllers;

use App\Exports\BooksExport;
use App\Imports\BooksImport;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('search');
        $sortField = $request->input('sortField', 'name');
        $sortDirection = $request->input('sortDirection', 'asc');
    
        $books = Book::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%$query%")
                ->orWhere('author', 'like', "%$query%");
        })
        ->orderBy($sortField, $sortDirection)
        ->paginate(10);
    
        return view('books.index', compact('books', 'sortField', 'sortDirection'));
    }

    public function create()
    {
        return view('books.create');
    }

    public function edit(Book $book)
    {
        return view('books.edit', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('books','name')->ignore($book->getKey())],
            'author' => ['required', 'string', 'max:255', Rule::unique('books','author')->ignore($book->getKey())],
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $book->update($attributes);

        $book->clearMediaCollection('cover');
        $book->addMedia($request->file('cover'))->toMediaCollection('cover');

        return redirect()->route('books.index')->with('success', 'Book updated successfully');
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => 'required|string|max:255|unique:books',
            'author' => 'required|string|max:255|unique:books',
            'cover' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $book = Book::create($attributes);
        $book->addMedia($request->file('cover'))->toMediaCollection('cover');

        return redirect()->route('books.index')->with('success', 'Book added successfully');
    }

    public function delete(Book $book)
    {
        $book->delete();
        return redirect()->route('books.index')->with('success', 'Book deleted successfully');

    }

    public function export() 
    {
        return Excel::download(new BooksExport, 'books.csv');
    }
    public function import(Request $request) 
    {
        $request->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $csvFile = $request->file('csvFile');

        Excel::import(new BooksImport, $csvFile);

        return redirect()->route('books.index')->with('success', 'Books imported successfully');
    }
}
