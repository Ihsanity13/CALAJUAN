@extends('layouts.app')

@section('title', $destination->name)
{{-- "full": no card around the page; it lays itself out --}}
@section('main_class', 'full')

@section('content')
@php
    // Google Maps directions from wherever the visitor is, to this place
    $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination='
        . $destination->latitude . ',' . $destination->longitude;
@endphp

<div class="container detail">
    <a class="back-link" href="{{ route('home') }}">
        <x-icon name="arrow-left" size="18" /> Explore
    </a>

    {{-- PHOTOS (primary first) with the Save button over the corner --}}
    <div class="detail-media">
        <x-photo-gallery :images="$destination->images" :name="$destination->name" />

        {{-- SAVE: guests are sent to log in. Saving itself comes with
             the Saved feature (Day 5b), which replaces this with a real toggle. --}}
        @guest
            <a class="save-btn" href="{{ route('login') }}">
                <x-icon name="heart" size="20" />
                <span>Save</span>
            </a>
        @endguest
    </div>

    {{-- HEADER: category, verified, name, rating, address --}}
    <header class="detail-head">
        <div class="dest-meta">
            <span class="tag">{{ $destination->category->name }}</span>
            @if ($destination->is_verified)
                <span class="badge badge-verified">
                    <x-icon name="verified" size="14" /> Verified
                </span>
            @endif
        </div>

        <h1>{{ $destination->name }}</h1>

        <p class="detail-rating">
            @if ($averageRating !== null)
                <x-icon name="star" size="18" fill="currentColor" class="rating-star" />
                <strong>{{ number_format($averageRating, 1) }}</strong>
                <span aria-hidden="true">·</span>
                <a href="#reviews">{{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</a>
            @else
                <span class="muted">No reviews yet</span>
            @endif
        </p>

        <p class="dest-address">
            <x-icon name="map-pin" size="16" />
            {{ $destination->address ?? 'Calauan, Laguna' }}
        </p>
    </header>

    {{-- Desktop: About + Reviews on the left, Location on the right.
         Phones: one column, in reading order. --}}
    <div class="detail-grid">
        <div class="detail-main">
            <section class="detail-section" aria-labelledby="about-heading">
                <h2 id="about-heading">About</h2>
                @if ($destination->description)
                    {{-- nl2br(e(...)): keep the line breaks the admin typed, safely escaped --}}
                    <p class="detail-text">{!! nl2br(e($destination->description)) !!}</p>
                @else
                    <p class="muted">No description yet.</p>
                @endif
            </section>

            <section class="detail-section" id="reviews" aria-labelledby="reviews-heading">
                <h2 id="reviews-heading">
                    Reviews
                    @if ($reviewCount > 0)
                        <span class="heading-count">({{ $reviewCount }})</span>
                    @endif
                </h2>

                {{-- The review form comes with the Reviews feature (Day 6) --}}
                @if ($reviewCount > 0)
                    <ul class="review-list" role="list">
                        @foreach ($destination->reviews as $review)
                            <li class="review">
                                <div class="review-head">
                                    <span class="review-avatar" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($review->user->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="review-name">{{ $review->user->name }}</p>
                                        <p class="review-date">
                                            <time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('M Y') }}</time>
                                        </p>
                                    </div>
                                    <x-stars :rating="$review->rating" class="review-stars" />
                                </div>
                                @if ($review->comment)
                                    <p class="review-comment">{{ $review->comment }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="empty-state">
                        <x-icon name="star" size="32" />
                        <h3>No reviews yet</h3>
                        <p>Been here? Reviews from visitors will show up here.</p>
                    </div>
                @endif
            </section>
        </div>

        <aside class="detail-aside" aria-labelledby="location-heading">
            {{-- Today's weather in Calauan. Hides itself if the weather service is down. --}}
            <x-weather-card />

            <section class="card location-card">
                <h2 id="location-heading">Location</h2>

                {{-- Small read-only map with the pin (public/js/map-view.js).
                     Decoration for sighted users; the address and Directions link carry the info. --}}
                <div class="map-view" data-map-view
                     data-lat="{{ $destination->latitude }}" data-lng="{{ $destination->longitude }}"
                     role="img" aria-label="Map showing where {{ $destination->name }} is"></div>

                <p class="dest-address">
                    <x-icon name="map-pin" size="16" />
                    {{ $destination->address ?? 'Calauan, Laguna' }}
                </p>

                {{-- Turn-by-turn directions happen in Google Maps (new tab).
                     A route preview inside our own map may be added later. --}}
                <a class="btn btn-block" href="{{ $directionsUrl }}" target="_blank" rel="noopener">
                    <x-icon name="map-pin" size="18" />
                    Open in Google Maps
                    <span class="sr-only">(opens in a new tab)</span>
                </a>
            </section>
        </aside>
    </div>
</div>
@endsection

{{-- Leaflet from its official CDN, same version and fingerprints as the admin map picker --}}
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/map-view.js') }}"></script>
@endpush
