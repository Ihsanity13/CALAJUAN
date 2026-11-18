@extends('layouts.app')

@section('title', 'Home')
{{-- "full" removes the white panel so the hero can span the whole width --}}
@section('main_class', 'full')

@section('content')
{{-- hero-fill: for owners and admins the banner is the whole page,
     so it stretches down to the footer instead of leaving an empty strip --}}
<section @class(['hero', 'on-dark', 'hero-fill' => ! $showFeatured])>
    <div class="container hero-inner">
        <div class="hero-copy">
            {{-- ONE BANNER PER ROLE ($heroRole and the numbers come from HomeController).
                 .accent colors the town's name pale gold. --}}
            {{-- ADMINS: what's waiting for review --}}
            @if ($heroRole === 'admin')
                    <p class="hero-eyebrow">
                        <x-icon name="grid" size="16" />
                        Admin · {{ auth()->user()->name }}
                    </p>
                    <h1>Keep <span class="accent">Calauan</span>'s guide up to date.</h1>
                    <p class="hero-lead">
                        @if ($approvalCount > 0)
                            {{ $approvalCount }} {{ \Illuminate\Support\Str::plural('submission', $approvalCount) }}
                            from business owners {{ $approvalCount === 1 ? 'is' : 'are' }} waiting for your review.
                        @else
                            Nothing is waiting for review.
                        @endif
                        {{ $liveCount }} {{ \Illuminate\Support\Str::plural('place', $liveCount) }} {{ $liveCount === 1 ? 'is' : 'are' }} live for visitors.
                    </p>
                    <div class="hero-actions">
                        <a class="btn btn-gold" href="{{ route('admin.dashboard') }}">Open the dashboard</a>
                        @if ($approvalCount > 0)
                            <a class="btn btn-outline" href="{{ route('admin.approvals.index') }}">Review submissions ({{ $approvalCount }})</a>
                        @else
                            <a class="btn btn-outline" href="{{ route('admin.destinations.index') }}">Manage destinations</a>
                        @endif
                    </div>

            {{-- BUSINESS OWNERS: their places, or where their verification stands --}}
            @elseif ($heroRole === 'owner')
                    <p class="hero-eyebrow">
                        <x-icon name="map-pin" size="16" />
                        Business owner · {{ auth()->user()->name }}
                    </p>
                    <h1>Put your business on the <span class="accent">Calauan</span> map.</h1>

                    @if ($verificationStatus === \App\Models\OwnerVerification::STATUS_APPROVED)
                        <p class="hero-lead">
                            @if ($liveCount + $waitingCount === 0)
                                Add your café, restaurant, resort or shop so visitors exploring Calauan can find it.
                            @else
                                @php
                                    // "You have 2 live places and 1 submission waiting for approval."
                                    $summary = 'You have ' . $liveCount . ' live ' . \Illuminate\Support\Str::plural('place', $liveCount);
                                    if ($waitingCount > 0) {
                                        $summary .= ' and ' . $waitingCount . ' ' . \Illuminate\Support\Str::plural('submission', $waitingCount) . ' waiting for approval';
                                    }
                                @endphp
                                {{ $summary }}.
                                Keep your hours, prices and photos up to date so visitors know what to expect.
                            @endif
                        </p>
                        <div class="hero-actions">
                            <a class="btn btn-gold" href="{{ route('owner.places.index') }}">Go to my places</a>
                            <a class="btn btn-outline" href="{{ route('owner.places.create') }}">Add a place</a>
                        </div>
                    @else
                        <p class="hero-lead">
                            @if ($verificationStatus === \App\Models\OwnerVerification::STATUS_PENDING)
                                We're checking your business details. You can add places as soon as they're approved, usually within 1 to 2 working days.
                            @elseif ($verificationStatus === \App\Models\OwnerVerification::STATUS_REJECTED)
                                We couldn't verify your business yet. See why, then send your details again.
                            @else
                                One more step: send your business permit so we can verify your business. Then you can add your places.
                            @endif
                        </p>
                        <div class="hero-actions">
                            <a class="btn btn-gold" href="{{ route('owner.verification.show') }}">
                                {{ $verificationStatus === \App\Models\OwnerVerification::STATUS_PENDING ? 'Check verification status' : 'Verify my business' }}
                            </a>
                        </div>
                    @endif

            {{-- VISITORS AND GUESTS --}}
            @else
                    <p class="hero-eyebrow">
                        <x-icon name="map-pin" size="16" />
                        Calauan, Laguna
                    </p>
                    <h1>Find where to go in <span class="accent">Calauan</span>.</h1>
                    <p class="hero-lead">Browse places around town, save the ones you like, and get suggestions based on what you enjoy.</p>
                    <div class="hero-actions">
                        @auth
                            <a class="btn btn-gold" href="{{ route('user.profile') }}">View my profile</a>
                        @else
                            <a class="btn btn-gold" href="{{ route('register') }}">Create an account</a>
                            <a class="btn btn-outline" href="{{ route('login') }}">Log in</a>
                        @endauth
                    </div>
            @endif
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

{{-- Visitors and guests only (see HomeController) --}}
@if ($showFeatured)
<section class="container section" id="featured">
    {{-- Today's weather in Calauan. Hides itself if the weather service is down. --}}
    <x-weather-card class="home-weather" />

    <div class="section-head">
        <h2>Featured places</h2>
        <p class="muted">Places picked by our team.</p>
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
            <img class="empty-logo" src="{{ asset('images/icon-512.png') }}" alt="" width="64" height="64">
            <h3>No featured places yet</h3>
            <p>Our team is still checking places around Calauan. Check back soon.</p>
        </div>
    @endif
</section>
@endif
@endsection