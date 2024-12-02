@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <h1 class="text-primary mb-4">Create User</h1>

        <form action="{{ route('users.store') }}" method="POST" class="bg-white p-4 rounded shadow">
            @csrf

            <!-- Name Field -->
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Enter full name" required>
            </div>

            <!-- Email Field -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter email address" required>
            </div>

            <!-- Password Field -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
            </div>

            <!-- Role Field -->
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="administrator">Administrator</option>
                    <option value="editor">Editor</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="d-flex">
                <button type="submit" class="btn btn-primary me-2">Create</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
