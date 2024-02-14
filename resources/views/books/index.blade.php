@extends('layouts.app')

@section('content')

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success" id="successAlert">
                {{ session('success') }}
            </div>
        @endif
        <a href="{{ route('books.create') }}" class="btn btn-success">Add</a>
        <a href="{{ route('books.export') }}" class="btn btn-warning">Export</a>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importCsvModal">Import from CSV</button>

          <!-- Search Form -->
        <form action="{{ route('books.index') }}" method="GET" class="mt-3 mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search books">
                <button type="submit" class="btn btn-outline-secondary">Search</button>
            </div>
        </form>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">
                        <a href="{{ route('books.index', ['sortField' => 'name', 'sortDirection' => $sortField === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}">
                            Name
                        </a>
                    </th>
                    <th scope="col">
                        <a href="{{ route('books.index', ['sortField' => 'author', 'sortDirection' => $sortField === 'author' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}">
                            Author
                        </a>
                    </th>
                    <th scope="col">Cover</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($books as $index => $book)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $book->name}}</td>
                        <td>{{ $book->author }}</td>
                        <td>
                            @if($book->getFirstMedia())
                                <img src="{{ $book->getFirstMediaUrl() }}" alt="Book Image" style="max-width: 100px; max-height: 100px;">
                            @else
                                No Image
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#viewbookModal" data-id="{{ $book->getKey() }}" data-name="{{ $book->name }}" data-author="{{ $book->author }}">
                                View
                            </button>
                            <a href="{{ route('books.edit', ['book' => $book]) }}" class="btn btn-warning">Edit</a>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $book->getKey() }})">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $books->links() }}
    </div>

    <!-- view Modal -->
    <div class="modal fade" id="viewbookModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Book Details</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>ID:</strong> <span id="modalBookId"></span></p>
                    <p><strong>Name:</strong> <span id="modalBookName"></span></p>
                    <p><strong>Author:</strong> <span id="modalBookAuthor"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importCsvModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Import Books from CSV</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('books.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Choose CSV File:</label>
                            <input type="file" class="form-control @error('csvFile') is-invalid @enderror" id="csvFile" name="csvFile" accept=".csv" required>
                            @error('csvFile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </form>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var viewModal = new bootstrap.Modal(document.getElementById('viewbookModal'));

            // JavaScript to handle view modal display
            var viewButtons = document.querySelectorAll('.btn-primary');
            viewButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var id = this.getAttribute('data-id');
                    var name = this.getAttribute('data-name');
                    var author = this.getAttribute('data-author');

                    // Update the view modal content
                    document.getElementById('modalBookId').innerText = id;
                    document.getElementById('modalBookName').innerText = name;
                    document.getElementById('modalBookAuthor').innerText = author;

                    // Show the view modal
                    viewModal.show();
                });
            });
        });

        // JavaScript to handle confirmation alert
        window.confirmDelete = function (bookId) {
            if (confirm('Are you sure you want to delete this book?')) {
                // Create a form element
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '/books/delete/' + bookId; // Replace with your actual delete route

                // Add CSRF token input
                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}'; // Use Blade syntax to get CSRF token
                form.appendChild(csrfToken);

                // Add a method override input for Laravel to recognize the DELETE request
                var methodOverride = document.createElement('input');
                methodOverride.type = 'hidden';
                methodOverride.name = '_method';
                methodOverride.value = 'POST';
                form.appendChild(methodOverride);

                // Add the form to the document and submit it
                document.body.appendChild(form);
                form.submit();
            }
            setTimeout(function(){
                document.getElementById('successAlert').style.display = 'none';
            }, 3000);
        };
    </script>
@endsection
