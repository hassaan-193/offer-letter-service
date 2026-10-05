<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Offer Letter Service — Fire Technical Services Portal" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Admin') — FTS Offer Letter Service</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}" />
    <link rel="shortcut icon" type="image/ico" href="{{ asset('images/logo.png') }}">
    @stack('styles')
</head>
<body class="hold-transition layout-top-nav text-sm">

@auth('admin')
    {{-- Top Navbar matching modern_portal --}}
    <header class="main-header">
        <div class="navbar-container">
            <div style="display: flex; align-items: center; gap: 30px;">
                <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
                    <img src="{{ asset('images/logo-top.png') }}" alt="Fire Technical Services" class="brand-image" onerror="this.src='{{ asset('images/logo.png') }}';">
                    <span class="brand-badge">Offer Letter</span>
                </a>

                <nav>
                    <ul class="navbar-nav">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.offers.index') }}" class="nav-link {{ request()->routeIs('admin.offers.index') || request()->routeIs('admin.offers.show') || request()->routeIs('admin.offers.edit') || request()->routeIs('admin.offers.preview') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Offer Letters
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.offers.create') }}" class="nav-link {{ request()->routeIs('admin.offers.create') ? 'active' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Create Offer
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="nav-right">
                <div class="user-badge">
                    <div class="user-avatar">{{ strtoupper(substr(auth('admin')->user()->name ?? 'A', 0, 1)) }}</div>
                    <span>{{ auth('admin')->user()->name ?? 'Admin' }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout" title="Sign out of portal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>
@endauth

    {{-- Main content --}}
    <main class="content-wrapper" style="{{ !auth('admin')->check() ? 'max-width: 100%; padding: 0;' : '' }}">
        @if(auth('admin')->check())
            @if(session('success'))
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        @endif

        @yield('content')
    </main>

@auth('admin')
    <footer class="main-footer text-sm d-print-none">
        <div class="navbar-container" style="display: flex; justify-content: space-between; align-items: center; height: auto;">
            <div>
                Copyright &copy; 2026 <strong><a href="http://fts_portal" style="color: var(--fts-maroon);">FTS Portal</a></strong> All rights reserved.
            </div>
            <div>
                <b>Offer Letter Service</b> v1.0
            </div>
        </div>
    </footer>
@endauth

    @stack('scripts')
</body>
</html>
