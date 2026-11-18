<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    {{-- viewport-fit=cover lets the bottom tab bar sit above the iPhone home bar --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2c4a2a">
    <title>@yield('title', 'Calajuan') · Calajuan</title>

    {{-- TAB ICON: the pineapple "C" from the logo (public/images). apple-touch-icon = phone home screen. --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icon-512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    {{-- Fonts from Google. If you're offline, the page falls back to Georgia and your system font. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Young+Serif&display=swap" rel="stylesheet">

    {{-- asset('css/app.css') builds the URL of the file in public/css/app.css --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Pages can add their own CSS here with @push('styles') (e.g. the map on the destination form) --}}
    @stack('styles')
</head>

{{-- $isAdmin / $isOwner / $isUser are worked out once here and reused by both navs below --}}
@php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $isOwner = auth()->check() && auth()->user()->isOwner();
    $isUser = auth()->check() && ! $isAdmin && ! $isOwner;
    // Admins: how many submissions wait, for the badge on the Approvals link
    $approvalBadge = $isAdmin ? \App\Models\User::pendingApprovalCount() : 0;
@endphp

<body>
    {{-- Lets keyboard users jump past the header straight to the page content --}}
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="site-header on-dark">
        <div class="container header-inner">
            {{-- THE LOGO (public/images/logo.png). alt="Calajuan" is the link's name for screen readers.
                 width/height reserve its space so the header doesn't jump while it loads. --}}
            <a class="brand" href="{{ route('home') }}">
                <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="Calajuan" width="360" height="159">
            </a>

            {{-- DESKTOP NAV: hidden on phones, where the bottom tab bar takes over.
                 aria-current="page" marks the link for the page you're on. --}}
            <nav class="top-nav" aria-label="Main">
                <a class="nav-link" href="{{ route('home') }}"
                   @if (request()->routeIs('home')) aria-current="page" @endif>Explore</a>

                @if ($isAdmin)
                    <a class="nav-link" href="{{ route('admin.dashboard') }}"
                       @if (request()->routeIs('admin.*') && ! request()->routeIs('admin.approvals.*')) aria-current="page" @endif>Dashboard</a>
                    <a class="nav-link" href="{{ route('admin.approvals.index') }}"
                       @if (request()->routeIs('admin.approvals.*')) aria-current="page" @endif>
                        Approvals
                        @if ($approvalBadge > 0)
                            <span class="nav-badge">{{ $approvalBadge }}<span class="sr-only"> waiting</span></span>
                        @endif
                    </a>
                @elseif ($isOwner)
                    <a class="nav-link" href="{{ route('owner.places.index') }}"
                       @if (request()->routeIs('owner.*')) aria-current="page" @endif>My places</a>
                @elseif ($isUser)
                    <a class="nav-link" href="{{ route('user.profile') }}#saved">Saved</a>
                    <a class="nav-link" href="{{ route('user.profile') }}"
                       @if (request()->routeIs('user.profile')) aria-current="page" @endif>Profile</a>
                @endif
            </nav>

            {{-- ACCOUNT ACTIONS: shown on every screen size --}}
            <div class="header-actions">
                {{-- @auth = shown only to logged-in users; @else = shown to guests --}}
                @auth
                    <span class="nav-user">{{ auth()->user()->name }}</span>

                    {{-- Logout is a POST form; @csrf adds the security token Laravel requires --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">
                            <x-icon name="logout" size="18" />
                            Log out
                        </button>
                    </form>
                @else
                    <a class="nav-link nav-login" href="{{ route('login') }}"
                       @if (request()->routeIs('login')) aria-current="page" @endif>Log in</a>
                    <a class="btn btn-gold btn-sm" href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- main_class: "narrow" (default, a centered card for forms),
         "wide" (a wider card for tables), "full" (no card, the page lays itself out) --}}
    <main id="main" class="site-main @yield('main_class', 'narrow')" tabindex="-1">
        {{-- One-time message from a controller's ->with('success', ...) --}}
        @if (session('success'))
            <div class="flash">
                <x-alert type="success">{{ session('success') }}</x-alert>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer on-dark">
        <div class="container footer-inner">
            <a class="brand brand-sm" href="{{ route('home') }}">
                <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="Calajuan" width="360" height="159" loading="lazy">
            </a>
            <p>A tourism guide for Calauan, Laguna.</p>
        </div>
    </footer>

    {{-- PHONE TAB BAR: fixed to the bottom of the screen, hidden on desktop.
         Guests are sent to log in for Saved and Profile. --}}
    <nav class="tab-bar" aria-label="Main">
        <a class="tab" href="{{ route('home') }}"
           @if (request()->routeIs('home')) aria-current="page" @endif>
            <x-icon name="compass" />
            <span>Explore</span>
        </a>

        @if ($isAdmin)
            <a class="tab" href="{{ route('admin.dashboard') }}"
               @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                <x-icon name="grid" />
                <span>Dashboard</span>
            </a>
            <a class="tab" href="{{ route('admin.destinations.index') }}"
               @if (request()->routeIs('admin.destinations.*')) aria-current="page" @endif>
                <x-icon name="map-pin" />
                <span>Places</span>
            </a>
            <a class="tab" href="{{ route('admin.approvals.index') }}"
               @if (request()->routeIs('admin.approvals.*')) aria-current="page" @endif>
                <span class="tab-icon">
                    <x-icon name="clock" />
                    @if ($approvalBadge > 0)
                        <span class="nav-badge tab-badge">{{ $approvalBadge }}<span class="sr-only"> waiting</span></span>
                    @endif
                </span>
                <span>Approvals</span>
            </a>
        @elseif ($isOwner)
            <a class="tab" href="{{ route('owner.places.index') }}"
               @if (request()->routeIs('owner.*')) aria-current="page" @endif>
                <x-icon name="map-pin" />
                <span>My places</span>
            </a>
        @else
            <a class="tab" href="{{ $isUser ? route('user.profile') . '#saved' : route('login') }}">
                <x-icon name="heart" />
                <span>Saved</span>
            </a>
            <a class="tab" href="{{ $isUser ? route('user.profile') : route('login') }}"
               @if (request()->routeIs('user.profile', 'login', 'register')) aria-current="page" @endif>
                <x-icon name="user" />
                <span>Profile</span>
            </a>
        @endif
    </nav>

    {{-- Pages can add their own JavaScript here with @push('scripts') --}}
    @stack('scripts')
</body>

</html>
