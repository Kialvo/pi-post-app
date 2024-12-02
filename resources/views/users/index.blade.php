@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <h1 class="text-primary mb-4">Manage Users</h1>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Create User Button -->
        <div class="mb-3">
            <a href="{{ route('users.create') }}" class="btn btn-primary">Create User</a>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
            <table id="users-table" class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <!-- Data is dynamically loaded -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- DataTables Script -->
    <script>
        $(document).ready(function () {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("users.index") }}',
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'role', name: 'role' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return `
                                <a href="/users/${row.id}/edit" class="btn btn-sm btn-primary me-2">Edit</a>
                                <a href="/users/${row.id}/confirm-delete" class="btn btn-sm btn-danger">Delete</a>
                            `;
                        }
                    }
                ],
                language: {
                    paginate: {
                        previous: '<i class="bi bi-arrow-left"></i>',
                        next: '<i class="bi bi-arrow-right"></i>',
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            });
        });
    </script>
@endsection
