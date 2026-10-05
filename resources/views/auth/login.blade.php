@extends('layouts.admin')
@section('title', 'Admin Login')

@push('styles')
<style>
body { 
    background: #f4f6f9; 
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-box {
    width: 420px;
    margin: 40px auto;
}
.login-card {
    background: #ffffff;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    border-top: 4px solid var(--fts-maroon);
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    overflow: hidden;
}
.login-card-header {
    background: #343a40;
    padding: 24px 20px;
    text-align: center;
    border-bottom: 2px solid var(--fts-maroon);
}
.login-card-header img {
    height: 48px;
    width: auto;
    object-fit: contain;
}
.login-card-body {
    padding: 30px 25px;
}
.login-title {
    font-size: 18px;
    font-weight: 700;
    color: #212529;
    text-align: center;
    margin-bottom: 6px;
}
.login-subtitle {
    font-size: 13px;
    color: #6c757d;
    text-align: center;
    margin-bottom: 25px;
}
.btn-login {
    width: 100%;
    padding: 10px;
    background-color: var(--fts-maroon);
    border: 1px solid var(--fts-maroon);
    border-radius: 4px;
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.2s;
}
.btn-login:hover {
    background-color: var(--fts-maroon-hover);
    border-color: var(--fts-maroon-hover);
}
</style>
@endpush

@section('content')
<div class="login-box">
    <div class="login-card">
        <div class="login-card-header">
            <img src="{{ asset('images/logo-top.png') }}" alt="Fire Technical Services" onerror="this.src='{{ asset('images/logo.png') }}';">
        </div>
        <div class="login-card-body">
            <div class="login-title">Admin Login</div>
            <div class="login-subtitle">Sign in to FTS Offer Letter Service portal</div>

            @if ($errors->any())
                <div class="alert alert-danger" style="padding: 10px; font-size: 12.5px; margin-bottom: 20px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus autocomplete="email" />
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="current-password" />
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: normal; margin: 0; cursor: pointer;">
                        <input type="checkbox" id="remember" name="remember" />
                        Remember me
                    </label>
                </div>
                <button type="submit" class="btn-login" id="login-btn">Sign In</button>
            </form>
        </div>
    </div>
</div>
@endsection
