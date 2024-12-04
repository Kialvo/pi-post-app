<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body style="font-family: 'Roboto', sans-serif; background-color: #f8f9fa;">
<div class="min-vh-100 d-flex flex-column">
    <!-- Navigation Bar -->
    @include('layouts.navigation')

    <!-- Page Heading -->
    @isset($header)
        <header class="bg-white shadow-sm py-3 mb-4">
            <div class="container">
                {{ $header }}
            </div>
        </header>
    @endisset

    <!-- Page Content -->
    <main class="flex-grow-1">
        {{ $slot }}
    </main>
</div>
<!-- Alpine.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.6/dist/cdn.min.js" defer></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
