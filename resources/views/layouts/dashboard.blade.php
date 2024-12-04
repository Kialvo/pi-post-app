<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Alpine.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.6/dist/cdn.min.js" defer></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body class="bg-gray-100 text-gray-900 font-sans">

<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Dashboard</a>

        <!-- User Dropdown -->
        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                {{ auth()->user()->name }} <!-- Display the logged-in user's name -->
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="d-flex">
    <!-- Sidebar -->
    <aside class="bg-primary text-white p-4 vh-100" style="width: 250px;">
        <h1 class="h4 mb-4">Admin Dashboard</h1>
        <ul class="nav flex-column">
            @if (auth()->user()->role === 'administrator')
                <li class="nav-item mb-3">
                    <a href="{{ route('users.index') }}" class="nav-link text-white fw-bold">
                        <i class="bi bi-people-fill me-2"></i> Users
                    </a>
                </li>
            @endif
            <li class="nav-item mb-3">
                <a href="{{ route('posts.index') }}" class="nav-link text-white fw-bold">
                    <i class="bi bi-file-earmark-text me-2"></i> Posts
                </a>
            </li>
            @if (auth()->user()->role === 'administrator')
                <li class="nav-item">
                    <a href="{{ route('scraper.form') }}" class="nav-link text-white fw-bold">
                        <i class="bi bi-cloud-download me-2"></i> Scraper
                    </a>
                </li>
            @endif
        </ul>
    </aside>


    <!-- Main Content -->
    <main class="w-100 p-6">
        @yield('content')
    </main>
</div>

</body>
</html>
