@extends('layouts.dashboard')

@section('content')
    <h1 class="text-4xl font-bold mb-6">Welcome, {{ auth()->user()->name }}</h1>

    @if (auth()->user()->role === 'administrator')
        <p class="text-lg">You have full access to manage Users, Posts, and the Scraper.</p>
    @elseif (auth()->user()->role === 'editor')
        <p class="text-lg">You can manage Posts and use the Scraper.</p>
    @endif
@endsection
