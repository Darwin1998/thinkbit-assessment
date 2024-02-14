@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="container">
            <form action="{{ route('books.update', ['book' => $book]) }}" method="POST" enctype="multipart/form-data">
                @csrf <!-- Add CSRF token for security -->
                @method("PATCH")
                <div class="row g-3">
                    <div class="col">
                        <input type="text" class="form-control" name="name" placeholder="Book name" aria-label="Book name" required value="{{ $book->name}}">
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col">
                        <input type="text" class="form-control" name="author" placeholder="Book author" aria-label="Book author" required value="{{ $book->author }}">
                        @error('author')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col">
                        <input type="file" class="form-control" name="cover" id="cover" accept="image/*" onchange="previewImage()">
                        @error('cover')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col">
                        @if($book->getFirstMedia('cover'))
                            <img src="{{ $book->getFirstMediaUrl('cover') }}" alt="Current Cover" style="max-width: 100%; display: block;">
                        @else
                            <p>No current cover image.</p>
                        @endif
                    </div>
                </div>
                <div class="footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function previewImage() {
        var input = document.getElementById('cover');
        var preview = document.getElementById('coverPreview');

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
