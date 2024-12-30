<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard</title>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <!-- Alpine.js (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.6/dist/cdn.min.js" defer></script>

    <!-- Bootstrap CSS/JS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
    ></script>

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
    />

    <!-- Optional custom styles -->
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* Left sidebar full height */
        .sidebar {
            width: 250px;
            background-color: #0d6efd; /* “bg-primary” color */
            color: white;
            overflow-y: auto; /* scroll if content grows */
        }

        /* User panel inside the sidebar (Pluto style) */
        .user-panel {
            text-align: center;
            padding: 1rem;
        }
        .user-panel img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 0.5rem;
        }
        .user-panel h5 {
            margin: 0.5rem 0 0.1rem;
            font-weight: 600;
        }
        .user-panel .online-status {
            color: #28a745; /* green text */
            font-size: 0.9rem;
        }

        /* The top nav sits to the right of the sidebar */
        .top-nav {
            height: 80px;
            background-color: #f8f9fa; /* “bg-light” color */
            border-bottom: 1px solid #ddd;
        }

        /* The main content area below the top nav */
        .main-content {
            flex: 1;            /* fill available space */
            overflow-y: auto;   /* scroll if needed */
            padding: 1.5rem;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans">

<!-- LAYOUT WRAPPER: sidebar (left) + right column (top nav + main) -->
<div class="d-flex" style="height: 100vh;">
    <!-- SIDEBAR -->
    <aside class="sidebar d-flex flex-column">
        <!-- Pluto-like user panel -->
        <div class="user-panel">
            <h5 class="mb-0">Admin Dashboard</h5>
        </div>
        <hr class="text-white"/>

        <!-- Remove the 'Admin Dashboard' heading here
             so it doesn't repeat in the sidebar -->
        <ul class="nav flex-column px-2">
            @if (auth()->user() && auth()->user()->role === 'administrator')
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
            @if (auth()->user() && auth()->user()->role === 'administrator')
                <li class="nav-item">
                    <a href="{{ route('scraper.form') }}" class="nav-link text-white fw-bold">
                        <i class="bi bi-cloud-download me-2"></i> Scraper
                    </a>
                </li>
            @endif
        </ul>
    </aside>

    <!-- RIGHT COLUMN: top nav at top, then main content below -->
    <div class="d-flex flex-column flex-grow-1">
        <!-- TOP NAV -->
        <nav class="top-nav d-flex align-items-center px-3">
            <!-- Left brand or nav items -->
            <div class="me-auto">
                <a class="navbar-brand d-flex align-items-center fw-bold" href="#">
                    <img
                        src="{{ asset('logo.png') }}"
                        alt="Passione Inter Logo"
                        style="height:70px; width:auto;"
                        class="me-2"
                    />
                </a>
            </div>

            <!-- Right icons (notifications, help, mail) + user dropdown -->
            <div class="d-flex align-items-center">
                <!-- Example icons -->
                <a href="#" class="text-secondary me-3">
                    <i class="bi bi-bell fs-5"></i>
                </a>
                <a href="#" class="text-secondary me-3">
                    <i class="bi bi-question-circle fs-5"></i>
                </a>
                <a href="#" class="text-secondary me-3">
                    <i class="bi bi-envelope fs-5"></i>
                </a>

                <!-- User dropdown -->
                <div class="dropdown">
                    <a
                        href="#"
                        class="nav-link dropdown-toggle text-dark d-flex align-items-center"
                        id="userDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <img
                            src="https://via.placeholder.com/35"
                            alt="Avatar"
                            class="rounded-circle me-2"
                            style="width:35px; height:35px; object-fit: cover;"
                        />
                        {{ auth()->user()->name ?? 'Admin User' }}
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

        <!-- MAIN CONTENT -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
