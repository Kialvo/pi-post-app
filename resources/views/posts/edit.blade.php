@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <h1 class="text-primary mb-4">Edit Post</h1>

        <form action="{{ route('posts.update', $post->id) }}" method="POST" class="bg-white p-4 rounded shadow">
            @csrf
            @method('PUT')

            <!-- Title Field -->
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $post->title) }}" required>
            </div>

            <!-- Translated Title Field -->
            <div class="mb-3">
                <label for="translated_title" class="form-label">Translated Title</label>
                <input type="text" name="translated_title" id="translated_title" class="form-control" value="{{ old('translated_title', $post->translated_title) }}">
            </div>

            <!-- Source Field -->
            <div class="mb-3">
                <label for="source" class="form-label">Source</label>
                <input type="text" name="source" id="source" class="form-control" value="{{ old('source', $post->source) }}" required>
            </div>

            <!-- URL Field -->
            <div class="mb-3">
                <label for="url" class="form-label">URL</label>
                <input type="url" name="url" id="url" class="form-control" value="{{ old('url', $post->url) }}" required>
            </div>

            <!-- Original Post Content Field -->
            <div class="mb-3">
                <label for="original_post_content" class="form-label">Original Post Content</label>
                <textarea name="original_post_content" id="original_post_content" class="form-control" rows="4">{{ old('original_post_content', $post->original_post_content) }}</textarea>
            </div>

            <!-- Translated Post Content Field -->
            <div class="mb-3">
                <label for="translated_post_content" class="form-label">Translated Post Content</label>
                <textarea name="translated_post_content" id="translated_post_content" class="form-control" rows="4">{{ old('translated_post_content', $post->translated_post_content) }}</textarea>
            </div>

            <!-- Summary Field -->
            <div class="mb-3">
                <label for="summary" class="form-label">Summary</label>
                <textarea name="summary" id="summary" class="form-control" rows="3">{{ old('summary', $post->summary) }}</textarea>
            </div>

            <!-- Date Field -->
            <!-- Date Field -->
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ old('date', \Carbon\Carbon::parse($post->date)->format('Y-m-d')) }}" required>
            </div>


            <!-- Submit Button -->
            <div class="d-flex">
                <button type="submit" class="btn btn-primary me-2">Update</button>
                <a href="{{ route('posts.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
