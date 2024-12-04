<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body style="font-family: 'Roboto', sans-serif; background-color: #f8f9fa;">
<div class="min-vh-100 d-flex flex-column justify-content-center align-items-center">
    <!-- Application Logo -->
    <div class="mb-4">
        <a href="/">
            <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="img-fluid" style="height: 80px;">
        </a>
    </div>

    <!-- Content Slot -->
    <div class="card shadow w-100" style="max-width: 400px;">
        <div class="card-body p-4">
            {{ $slot }}
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Alpine.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.6/dist/cdn.min.js" defer></script>

</body>
</html>
