{{-- DESTINATION CARD: used on the home page now, and later on Explore and Saved.
     Usage: <x-destination-card :destination="$destination" />
     Expects the destination loaded with: category, primaryImage,
     withAvg('reviews', 'rating'), withCount('reviews'). --}}
@props(['destination'])

@php
    // reviews_avg_rating is null when nobody has reviewed the place yet
    $avg = $destination->reviews_avg_rating;
    $count = $destination->reviews_count ?? 0;
@endphp

<article {{ $attributes->merge(['class' => 'card card-lift dest-card']) }}>
    <div class="dest-media">
        {{-- The primary photo, or the gold lattice placeholder when there's none.
             loading="lazy": the browser only downloads the photo when the card
             gets near the screen. width/height stop the page jumping while it loads.
             The placeholder is decoration, so screen readers skip it. --}}
        @if ($destination->primaryImage)
            <img src="{{ $destination->primaryImage->url }}"
                 alt="Photo of {{ $destination->name }}"
                 loading="lazy" decoding="async" width="800" height="600">
        @else
            <div class="img-placeholder" aria-hidden="true"></div>
        @endif

        <span class="tag">{{ $destination->category->name }}</span>
    </div>

    <div class="dest-body">
        {{-- The name is the link. CSS (.dest-link::after) stretches its click area
             over the whole card, so tapping anywhere on the card opens the page. --}}
        <h3>
            <a class="dest-link" href="{{ route('destinations.show', $destination) }}">{{ $destination->name }}</a>
        </h3>

        <div class="dest-meta">
            @if ($destination->is_verified)
                <span class="badge badge-verified">
                    <x-icon name="verified" size="14" />
                    Verified
                </span>
            @endif

            @if ($avg)
                {{-- The visible "4.5 (12)" is short; screen readers hear the full sentence --}}
                <span class="rating">
                    <x-icon name="star" size="16" fill="currentColor" />
                    <span aria-hidden="true">{{ number_format($avg, 1) }} ({{ $count }})</span>
                    <span class="sr-only">Rated {{ number_format($avg, 1) }} out of 5 from {{ $count }} {{ \Illuminate\Support\Str::plural('review', $count) }}</span>
                </span>
            @else
                <span class="rating rating-none">No reviews yet</span>
            @endif
        </div>

        <p class="dest-address">
            <x-icon name="map-pin" size="16" />
            {{ $destination->address ?? 'Calauan, Laguna' }}
        </p>

        @if ($destination->description)
            <p class="dest-desc">{{ \Illuminate\Support\Str::limit($destination->description, 110) }}</p>
        @endif
    </div>
</article>
