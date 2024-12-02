@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <h1 class="text-primary mb-4">Manage Posts</h1>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table id="posts-table" class="table table-striped table-bordered">
                <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Source</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <!-- Data loaded dynamically by DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#posts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("posts.index") }}',
                columns: [
                    { data: 'title', name: 'title' },
                    { data: 'source', name: 'source' },
                    { data: 'date', name: 'date' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                    }
                ],
                language: {
                    paginate: {
                        previous: '<i class="bi bi-arrow-left"></i>',
                        next: '<i class="bi bi-arrow-right"></i>',
                    }
                }
            });
        });
    </script>
@endsection
