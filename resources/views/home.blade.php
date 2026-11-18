@extends('layouts.app')

@section('title', 'Home')
{{-- "full" removes the white panel so the hero can span the whole width --}}
@section('main_class', 'full')

@section('content')
<section class="hero on-dark">
    <div class="container hero-inner">
        <div class="hero-copy">
            <p class="hero-eyebrow">
                <x-icon name="map-pin" size="16" />
                Calauan, Laguna
            </p>

            {{-- .accent colors the town's name pale gold --}}
            <h1>Find where to go in <span class="accent">Calauan</span>.</h1>
            <p class="hero-lead">Browse places around town, save the ones you like, and get suggestions based on what you enjoy.</p>

            <div class="hero-actions">
                @auth
                @if (auth()->user()->isAdmin())
                <a class="btn btn-gold" href="{{ route('admin.dashboard') }}">Open the admin dashboard</a>
                @else
                <a class="btn btn-gold" href="{{ route('user.profile') }}">View my profile</a>
                @endif
                @else
                <a class="btn btn-gold" href="{{ route('register') }}">Create an account</a>
                <a class="btn btn-outline" href="{{ route('login') }}">Log in</a>
                @endauth
            </div>
        </div>

        {{-- THE PINEAPPLE: the one bold element on the site.
             The "skin" pattern draws the diamond lattice.
             aria-hidden: it's decoration, so screen readers skip it. --}}
        <svg class="hero-art" viewBox="0 0 220 320" aria-hidden="true" focusable="false">
            <defs>
                {{-- The two gradients match --grad-crown and --grad-fruit (160deg).
                     Their colors come from the CSS tokens (see .hero-art in app.css). --}}
                <linearGradient id="crown" gradientUnits="userSpaceOnUse" x1="90" y1="4" x2="130" y2="114">
                    <stop offset="0%" class="crown-1" />
                    <stop offset="45%" class="crown-2" />
                    <stop offset="100%" class="crown-3" />
                </linearGradient>
                <linearGradient id="fruit" gradientUnits="userSpaceOnUse" x1="76" y1="111" x2="144" y2="299">
                    <stop offset="0%" class="fruit-1" />
                    <stop offset="35%" class="fruit-2" />
                    <stop offset="70%" class="fruit-3" />
                    <stop offset="100%" class="fruit-4" />
                </linearGradient>

                <pattern id="skin" width="36" height="36" patternUnits="userSpaceOnUse">
                    {{-- Two diagonal lines per tile make the diamonds;
                             the dots are the pineapple's "eyes" --}}
                    <path class="lattice-line" d="M0 0L36 36M36 0L0 36" stroke-width="2.5" fill="none" />
                    <circle class="lattice-eye" cx="18" cy="0" r="2.4" />
                    <circle class="lattice-eye" cx="18" cy="36" r="2.4" />
                    <circle class="lattice-eye" cx="0" cy="18" r="2.4" />
                    <circle class="lattice-eye" cx="36" cy="18" r="2.4" />
                </pattern>
            </defs>

            {{-- A soft pale disc behind the fruit, so it stands out from the green --}}
            <circle class="halo" cx="110" cy="190" r="104" />

            {{-- Leaves, back to front: outer, middle, center. One crown gradient
                 runs across all of them: light tips, dark base. --}}
            <path d="M100 114 C62 96 34 70 20 40 C52 48 90 76 100 114Z" fill="url(#crown)" />
            <path d="M120 114 C158 96 186 70 200 40 C168 48 130 76 120 114Z" fill="url(#crown)" />
            <path d="M104 112 C78 84 66 52 66 18 C90 40 104 74 104 112Z" fill="url(#crown)" />
            <path d="M116 112 C142 84 154 52 154 18 C130 40 116 74 116 112Z" fill="url(#crown)" />
            <path d="M110 114 C94 76 98 38 110 4 C122 38 126 76 110 114Z" fill="url(#crown)" />

            {{-- The fruit: the ripening gradient, then the lattice drawn over it --}}
            <ellipse cx="110" cy="205" rx="78" ry="100" fill="url(#fruit)" />
            <ellipse cx="110" cy="205" rx="78" ry="100" fill="url(#skin)" />
        </svg>
    </div>
</section>

<section class="container section" id="featured">
    {{-- Today's weather in Calauan. Hides itself if the weather service is down. --}}
    <x-weather-card class="home-weather" />

    <div class="section-head">
        <h2>Featured places</h2>
        <p class="muted">Verified places picked by our team.</p>
    </div>

    {{-- @if / @else instead of @forelse, because the grid wrapper
         should only appear when there are cards to put in it --}}
    @if ($featured->isNotEmpty())
        <div class="dest-grid">
            @foreach ($featured as $destination)
                <x-destination-card :destination="$destination" />
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <span class="brand-mark brand-mark-lg" aria-hidden="true"></span>
            <h3>No featured places yet</h3>
            <p>Our team is still checking places around Calauan. Check back soon.</p>
        </div>
    @endif
</section>
@endsection