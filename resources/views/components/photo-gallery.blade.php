{{-- PHOTO GALLERY: swipe through a destination's photos (no library).
     Usage (Day 4 detail page): <x-photo-gallery :images="$destination->images" :name="$destination->name" />
     - Swiping works with CSS scroll-snap alone: each photo "snaps" into place.
     - public/js/gallery.js adds the "1 / 3" counter and the previous/next buttons.
     - No photos: shows the gold lattice placeholder instead. --}}
@props(['images', 'name'])

@php $total = $images->count(); @endphp

@if ($total === 0)
    <div {{ $attributes->merge(['class' => 'gallery gallery-empty img-placeholder']) }} aria-hidden="true"></div>
@else
    {{-- aria-roledescription tells screen readers this is a carousel of slides --}}
    <section {{ $attributes->merge(['class' => 'gallery']) }}
             data-gallery
             aria-roledescription="carousel"
             aria-label="Photos of {{ $name }}">

        {{-- tabindex="0": keyboard users can focus it and use the arrow keys to scroll --}}
        <ul class="gallery-track" data-gallery-track tabindex="0" role="list">
            @foreach ($images as $image)
                <li class="gallery-slide" aria-roledescription="slide"
                    aria-label="{{ $loop->iteration }} of {{ $total }}">
                    {{-- The first photo loads right away; the rest only when swiped near --}}
                    <img src="{{ $image->url }}"
                         alt="Photo {{ $loop->iteration }} of {{ $name }}"
                         loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                         decoding="async" width="1200" height="900">
                </li>
            @endforeach
        </ul>

        @if ($total > 1)
            <button type="button" class="gallery-btn gallery-prev" data-gallery-prev aria-label="Previous photo">
                <x-icon name="arrow-left" size="20" />
            </button>
            <button type="button" class="gallery-btn gallery-next" data-gallery-next aria-label="Next photo">
                <x-icon name="arrow-right" size="20" />
            </button>

            {{-- aria-hidden: each slide already says "2 of 3" to screen readers --}}
            <p class="gallery-counter" aria-hidden="true">
                <span data-gallery-current>1</span> / {{ $total }}
            </p>
        @endif
    </section>

    {{-- @once: even with two galleries on a page, the script is added only once --}}
    @once
        @push('scripts')
            <script src="{{ asset('js/gallery.js') }}" defer></script>
        @endpush
    @endonce
@endif
