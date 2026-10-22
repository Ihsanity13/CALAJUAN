@extends('layouts.app')

@section('title', 'Home')
{{-- "full" removes the white panel so the hero can span the whole width --}}
@section('main_class', 'full')

@section('content')
<section class="hero">
    <div class="hero-inner">
        <div>
            <h1>Find where to go in Calauan.</h1>
            <p>Browse places around town, save the ones you like, and get suggestions based on what you enjoy.</p>

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
                 The "skin" pattern draws the diamond lattice. --}}
        <svg class="hero-art" viewBox="0 0 220 320" role="img" aria-label="Illustration of a pineapple">
            <defs>
                <pattern id="skin" width="36" height="36" patternUnits="userSpaceOnUse">
                    {{-- Two diagonal lines per tile make the diamonds;
                             the dots are the pineapple's "eyes" --}}
                    <path d="M0 0L36 36M36 0L0 36" stroke="#8a5a12" stroke-width="2.5" fill="none" />
                    <circle cx="18" cy="0" r="2.4" fill="#8a5a12" />
                    <circle cx="18" cy="36" r="2.4" fill="#8a5a12" />
                    <circle cx="0" cy="18" r="2.4" fill="#8a5a12" />
                    <circle cx="36" cy="18" r="2.4" fill="#8a5a12" />
                </pattern>
            </defs>

            {{-- Leaves, back to front: outer, middle, center --}}
            <path d="M100 114 C62 96 34 70 20 40 C52 48 90 76 100 114Z" fill="#4fb391" />
            <path d="M120 114 C158 96 186 70 200 40 C168 48 130 76 120 114Z" fill="#4fb391" />
            <path d="M104 112 C78 84 66 52 66 18 C90 40 104 74 104 112Z" fill="#6ccba8" />
            <path d="M116 112 C142 84 154 52 154 18 C130 40 116 74 116 112Z" fill="#6ccba8" />
            <path d="M110 114 C94 76 98 38 110 4 C122 38 126 76 110 114Z" fill="#9be3c6" />

            {{-- The fruit: a gold oval, then the lattice drawn over it --}}
            <ellipse cx="110" cy="205" rx="78" ry="100" fill="#f2b61f" />
            <ellipse cx="110" cy="205" rx="78" ry="100" fill="url(#skin)" />
        </svg>
    </div>
</section>

<section class="section" id="featured">
    <h2>Featured places</h2>
    <p class="muted">Verified places picked by our team.</p>

    <div class="places">
        {{-- @forelse loops through the list, and runs @empty if there's nothing --}}
        @forelse ($featured as $destination)
        <article class="place">
            <div>
                <h3>{{ $destination->name }}</h3>
                <p class="muted">{{ $destination->address ?? 'Calauan, Laguna' }}</p>
                <p>{{ \Illuminate\Support\Str::limit($destination->description ?? '', 110) }}</p>
            </div>
            <span class="tag">{{ $destination->category->name }}</span>
        </article>
        @empty
        <div class="empty">No featured places yet. Check back soon.</div>
        @endforelse
    </div>
</section>
@endsection