@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <h1 class="text-primary mb-4">Edit Post</h1>

        <form action="{{ route('posts.update', $post->id) }}" method="POST" class="bg-white p-4 rounded shadow">
            @csrf
            @method('PUT')

            <!-- Title Field -->
            <div class="mb-3">
                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $post->title) }}" required>
                @error('title')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <!-- Translated Title Field -->
            <div class="mb-3">
                <label for="translated_title" class="form-label">Translated Title</label>
                <input type="text" name="translated_title" id="translated_title" class="form-control @error('translated_title') is-invalid @enderror" value="{{ old('translated_title', $post->translated_title) }}">
                @error('translated_title')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <!-- Source Field -->
            <div class="mb-3">
                <label for="source" class="form-label">Source <span class="text-danger">*</span></label>
                <input type="text" name="source" id="source" class="form-control @error('source') is-invalid @enderror" value="{{ old('source', $post->source) }}" required>
                @error('source')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <!-- URL Field as Clickable Link and Hidden Input -->
            <div class="mb-3">
                <label for="url" class="form-label">URL <span class="text-danger">*</span></label>
                @if ($post->url)
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                        <a href="{{ $post->url }}" target="_blank" class="form-control text-decoration-none">{{ $post->url }}</a>
                        <input type="hidden" name="url" value="{{ $post->url }}">
                    </div>
                @else
                    <!-- If URL is not set, provide a placeholder or alternative UI -->
                    <input type="url" name="url" id="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}" required>
                    @error('url')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                @endif
                @if ($post->url)
                    <small class="form-text text-muted">
                        Click the URL to open it in a new tab.
                    </small>
                @endif
            </div>

            <!-- Original Post Content Field -->
            <div class="mb-3">
                <label for="original_post_content" class="form-label">Original Post Content</label>
                <textarea name="original_post_content" id="original_post_content" class="form-control @error('original_post_content') is-invalid @enderror" rows="4">{{ old('original_post_content', $post->original_post_content) }}</textarea>
                @error('original_post_content')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <!-- Translated Post Content Field -->
            <div class="mb-3">
                <label for="translated_post_content" class="form-label">Translated Post Content</label>
                <textarea name="translated_post_content" id="translated_post_content" class="form-control @error('translated_post_content') is-invalid @enderror" rows="4">{{ old('translated_post_content', $post->translated_post_content) }}</textarea>
                @error('translated_post_content')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <!-- Summary Field -->
            <div class="mb-3">
                <label for="summary" class="form-label">Modified Content</label>
                <textarea name="summary" id="summary" class="form-control @error('summary') is-invalid @enderror" rows="3">{{ old('summary', $post->summary) }}</textarea>
                @error('summary')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <!-- Date Field -->
            <div class="mb-3">
                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', \Carbon\Carbon::parse($post->date)->format('Y-m-d')) }}" required>
                @error('date')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="d-flex">
                <button type="submit" class="btn btn-primary me-2">Update</button>
                <a href="{{ route('posts.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
