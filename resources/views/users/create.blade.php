@extends('layouts.app')

@section('content')
<style>
    .dash-card {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(56, 189, 248, 0.2);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }
    .form-control-custom {
        background: rgba(30, 41, 59, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #ffffff;
        border-radius: 8px;
        padding: 12px 16px;
    }
    .form-control-custom:focus {
        background: rgba(30, 41, 59, 1);
        border-color: #38bdf8;
        box-shadow: 0 0 12px rgba(56, 189, 248, 0.4);
        color: #ffffff;
    }
</style>
<!-- <script src="http://localhost:3000/tracker.js" defer></script> -->
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="dash-card p-4">
                <h4 class="text-light font-weight-bold mb-3">👤 Add New Admin User</h4>
                <p class="text-secondary small mb-4">Provision a new account for portal administration.</p>

                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="name" class="text-light small font-weight-bold">Admin Full Name</label>
                        <input id="name" type="text" class="form-control form-control-custom @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus placeholder="Admin Name">

                        @error('name')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="email" class="text-light small font-weight-bold">Admin Email Address</label>
                        <input id="email" type="email" class="form-control form-control-custom @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required placeholder="admin@3duiq.com">

                        @error('email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="text-light small font-weight-bold">Password</label>
                        <input id="password" type="password" class="form-control form-control-custom @error('password') is-invalid @enderror" name="password" required placeholder="Minimum 8 characters">

                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="password-confirm" class="text-light small font-weight-bold">Confirm Password</label>
                        <input id="password-confirm" type="password" class="form-control form-control-custom" name="password_confirmation" required placeholder="Re-enter password">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-info font-weight-bold px-4">
                            Save Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
