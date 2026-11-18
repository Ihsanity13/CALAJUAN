{{-- STARS: a 1-5 star rating drawn as 5 small stars (filled up to the rating).
     Usage: <x-stars :rating="4" />
     Screen readers hear "Rated 4 out of 5" instead of five separate icons. --}}
@props(['rating', 'size' => 16])

@php $rounded = (int) round($rating); @endphp

<span {{ $attributes->merge(['class' => 'stars']) }} role="img" aria-label="Rated {{ $rounded }} out of 5">
    @for ($i = 1; $i <= 5; $i++)
        <x-icon name="star" :size="$size"
                :class="$i <= $rounded ? 'star-on' : 'star-off'"
                :fill="$i <= $rounded ? 'currentColor' : 'none'" />
    @endfor
</span>
