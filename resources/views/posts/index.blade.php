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
                    <th>Flag</th> <!-- New Flag Column -->
                </tr>
                </thead>
                <tbody>
                <!-- Data loaded dynamically by DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Toast Notification for User Feedback -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="flag-toast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Flag status updated successfully.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Include necessary scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS and Bootstrap 5 Integration -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Bootstrap Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.js"></script>
    <!-- Bootstrap JS (for Toasts) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            var table = $('#posts-table').DataTable({
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
                        searchable: false
                    },
                    {
                        data: 'flag',
                        name: 'flag',
                        orderable: true,
                        searchable: false,
                        render: function(data, type, row) {
                            var isFlagged = data === 1; // true if 1, false if 0
                            var flagClass = isFlagged ? 'text-danger' : 'text-secondary';
                            var flagTitle = isFlagged ? 'Unflag this post' : 'Flag this post';
                            return `
                            <button class="btn btn-link flag-btn ${flagClass}" data-id="${row.id}" title="${flagTitle}">
                                <i class="bi bi-flag-fill" style="font-size: 1.5rem;"></i>
                            </button>
                        `;
                        }
                    },
                ],
                language: {
                    paginate: {
                        previous: '<i class="bi bi-arrow-left"></i>',
                        next: '<i class="bi bi-arrow-right"></i>',
                    }
                },
                order: [[2, 'desc']], // Default sort by date column descending
                // Optional: Adjust initial sorting as needed
            });

            // Handle Flag Button Click
            $('#posts-table').on('click', '.flag-btn', function() {
                var button = $(this);
                var postId = button.data('id');
                var isFlagged = button.hasClass('text-danger');

                // Optimistically toggle the flag status visually
                if (isFlagged) {
                    button.removeClass('text-danger').addClass('text-secondary');
                } else {
                    button.removeClass('text-secondary').addClass('text-danger');
                }

                // Send AJAX request to backend to toggle flag status
                $.ajax({
                    url: '{{ route("posts.toggleFlag") }}',
                    method: 'POST',
                    data: {
                        id: postId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success toast
                            var toastEl = document.getElementById('flag-toast');
                            var toast = new bootstrap.Toast(toastEl);
                            toast.show();

                            // Optionally, update the button's title attribute
                            var newTitle = response.is_flagged ? 'Unflag this post' : 'Flag this post';
                            button.attr('title', newTitle);
                        } else {
                            // Revert the visual change if failed
                            if (isFlagged) {
                                button.removeClass('text-secondary').addClass('text-danger');
                            } else {
                                button.removeClass('text-danger').addClass('text-secondary');
                            }
                            alert(response.message || 'Failed to update flag status.');
                        }
                    },
                    error: function(xhr) {
                        // Revert the visual change if AJAX request fails
                        if (isFlagged) {
                            button.removeClass('text-secondary').addClass('text-danger');
                        } else {
                            button.removeClass('text-danger').addClass('text-secondary');
                        }

                        var errorMessage = 'An error occurred while updating flag status.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        alert(errorMessage);
                    }
                });
            });
        });
    </script>

@endsection
