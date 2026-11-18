@extends('layouts.app')

@section('title', $destination->name)
{{-- "full": no card around the page; it lays itself out --}}
@section('main_class', 'full')

@section('content')
@php
    // Google Maps directions from wherever the visitor is, to this place
    $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination='
        . $destination->latitude . ',' . $destination->longitude;

    // Opening hours: open right now (true/false), or null when the hours aren't listed
    $openNow = $destination->isOpenAt();
    $today = now(\App\Models\Destination::TIMEZONE)->dayOfWeek;
    $hoursByDay = $destination->hours->keyBy('day');

    $price = $destination->price();
    $hasContact = $destination->phone || $destination->email || $destination->website || $destination->facebook;
@endphp

<div class="container detail">
    <a class="back-link" href="{{ route('home') }}">
        <x-icon name="arrow-left" size="18" /> Explore
    </a>

    {{-- PREVIEW: only the owner and admins can open a place that isn't approved --}}
    @unless ($destination->isApproved())
        <x-alert type="warning" class="detail-preview">
            <strong>Preview.</strong>
            @if ($destination->status === \App\Models\Destination::STATUS_REJECTED)
                This place was not approved, so visitors can't see it.
            @else
                This place is waiting for approval. Visitors can't see it yet.
            @endif
        </x-alert>
    @endunless

    {{-- PHOTOS (primary first) with the Save button over the corner --}}
    <div class="detail-media">
        <x-photo-gallery :images="$destination->images" :name="$destination->name" />

        {{-- SAVE: guests are sent to log in; visitors get the real toggle below.
             Owners and admins don't save places, so they see no button. --}}
        @guest
            <a class="save-btn" href="{{ route('login') }}">
                <x-icon name="heart" size="20" />
                <span>Save</span>
                <span class="sr-only">(log in to save places)</span>
            </a>
        @endguest

        {{-- VISITORS: one button saves / un-saves. aria-pressed tells screen readers
             whether it's on. Only live places can be saved. --}}
        @if ($isSaved !== null && $destination->isApproved())
            <form method="POST" action="{{ route('destinations.save', $destination) }}">
                @csrf
                <button type="submit" @class(['save-btn', 'is-saved' => $isSaved]) aria-pressed="{{ $isSaved ? 'true' : 'false' }}">
                    <x-icon name="heart" size="20" :fill="$isSaved ? 'currentColor' : 'none'" />
                    <span>{{ $isSaved ? 'Saved' : 'Save' }}</span>
                </button>
            </form>
        @endif
    </div>

    {{-- HEADER: category, labels, name, rating, open now, address --}}
    <header class="detail-head">
        <div class="dest-meta">
            <span class="tag">{{ $destination->category->name }}</span>
            <x-place-labels :destination="$destination" />
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

            {{-- Open now / Closed now (only when the hours are listed) --}}
            @if ($openNow !== null)
                <span aria-hidden="true">·</span>
                <a href="#hours-heading" @class(['open-status', 'is-open' => $openNow, 'is-closed' => ! $openNow])>
                    {{ $openNow ? 'Open now' : 'Closed now' }}
                </a>
            @endif

            @if ($price)
                <span aria-hidden="true">·</span>
                <span class="price-level">{{ $price['symbol'] }}<span class="sr-only"> ({{ $price['label'] }})</span></span>
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

                {{-- EXPERIENCES: the place's interests (Nature, Adventure...), as visitors see them --}}
                @if ($destination->interests->isNotEmpty())
                    <h3 class="detail-subhead">Experiences</h3>
                    <ul class="interest-list" role="list">
                        @foreach ($destination->interests as $interest)
                            <x-interest-pill :interest="$interest" tag="li" />
                        @endforeach
                    </ul>
                @endif

                @if ($price || $destination->price_note)
                    <h3 class="detail-subhead">Prices</h3>
                    <p class="price-line">
                        @if ($price)
                            <strong>{{ $price['symbol'] === $price['label'] ? $price['label'] : $price['symbol'] . ' · ' . $price['label'] }}</strong>
                        @endif
                        @if ($price && $destination->price_note)
                            <span aria-hidden="true">·</span>
                        @endif
                        @if ($destination->price_note)
                            {{ $destination->price_note }}
                        @endif
                    </p>
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

        <aside class="detail-aside" aria-label="Visit information">
            {{-- OPENING HOURS: Monday first, today highlighted --}}
            <section class="card info-card" aria-labelledby="hours-heading">
                <h2 id="hours-heading">
                    <x-icon name="clock" size="20" />
                    Opening hours
                </h2>

                @if ($hoursByDay->isEmpty())
                    <p class="muted">Hours not listed yet. Contact the place before visiting.</p>
                @else
                    <p @class(['open-status', 'is-open' => $openNow, 'is-closed' => ! $openNow])>
                        {{ $openNow ? 'Open now' : 'Closed now' }}
                    </p>
                    <dl class="hours-table">
                        @foreach (\App\Models\DestinationHour::WEEK_ORDER as $day)
                            <div @class(['hours-line', 'is-today' => $day === $today])>
                                <dt>
                                    {{ \App\Models\DestinationHour::DAYS[$day] }}
                                    @if ($day === $today)
                                        <span class="sr-only">(today)</span>
                                    @endif
                                </dt>
                                <dd>{{ $hoursByDay->get($day)?->label() ?? 'Closed' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </section>

            {{-- CONTACT: tap to call, email, or open the website / Facebook page --}}
            @if ($hasContact)
                <section class="card info-card" aria-labelledby="contact-heading">
                    <h2 id="contact-heading">
                        <x-icon name="phone" size="20" />
                        Contact
                    </h2>
                    <ul class="contact-list" role="list">
                        @if ($destination->phone)
                            <li>
                                {{-- tel: links need the number without spaces --}}
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', explode('/', $destination->phone)[0]) }}">
                                    <x-icon name="phone" size="18" /> {{ $destination->phone }}
                                </a>
                            </li>
                        @endif
                        @if ($destination->email)
                            <li>
                                <a href="mailto:{{ $destination->email }}">
                                    <x-icon name="mail" size="18" /> {{ $destination->email }}
                                </a>
                            </li>
                        @endif
                        @if ($destination->website)
                            <li>
                                <a href="{{ $destination->website }}" target="_blank" rel="noopener nofollow">
                                    <x-icon name="globe" size="18" /> Website
                                    <span class="sr-only">(opens in a new tab)</span>
                                </a>
                            </li>
                        @endif
                        @if ($destination->facebook)
                            <li>
                                <a href="{{ $destination->facebook }}" target="_blank" rel="noopener nofollow">
                                    <x-icon name="facebook" size="18" /> Facebook page
                                    <span class="sr-only">(opens in a new tab)</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </section>
            @endif

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
