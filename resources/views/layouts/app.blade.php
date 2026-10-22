<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Calajuan')</title>

    {{-- Fonts from Google. If you're offline, the page falls back to Georgia and your system font. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600&family=Young+Serif&display=swap" rel="stylesheet">

    {{-- asset('css/app.css') builds the URL of the file in public/css/app.css --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}">
            {{-- aria-hidden: screen readers skip this purely decorative logo --}}
            <span class="brand-mark" aria-hidden="true"></span>
            Calajuan
        </a>

        <nav class="nav">
            {{-- @auth = shown only to logged-in users; @else = shown to guests --}}
            @auth
            {{-- Show the link that fits the user's role --}}
            @if (auth()->user()->isAdmin())
            <a class="nav-link" href="{{ route('admin.dashboard') }}">Admin dashboard</a>
            @else
            <a class="nav-link" href="{{ route('user.profile') }}">My profile</a>
            @endif

            <span class="nav-user">{{ auth()->user()->name }}</span>

            {{-- Logout is a POST form; @csrf adds the security token Laravel requires --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline">Log out</button>
            </form>
            @else
            <a class="nav-link" href="{{ route('login') }}">Log in</a>
            <a class="btn btn-gold" href="{{ route('register') }}">Register</a>
            @endauth
        </nav>
    </header>

    <main class="@yield('main_class')">
        {{-- One-time message from a controller's ->with('success', ...) --}}
        @if (session('success'))
        <div class="success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        Calajuan, a tourism guide for Calauan, Laguna.
    </footer>
</body>

</html>