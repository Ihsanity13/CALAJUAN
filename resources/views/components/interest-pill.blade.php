{{-- ONE EXPERIENCE PILL (an interest, in its color).
     Usage: <x-interest-pill :interest="$interest" />   or as a list item: <x-interest-pill :interest="$i" tag="li" />
     The colors are in app.css (.interest-green, .interest-orange ...); every one
     keeps dark text on a pale background, so it stays readable. --}}
@props(['interest', 'tag' => 'span'])

<{{ $tag }} {{ $attributes->merge(['class' => 'interest-pill interest-' . ($interest->color ?? 'gray')]) }}>{{ $interest->name }}</{{ $tag }}>
