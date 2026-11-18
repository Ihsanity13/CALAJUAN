{{-- PLACE LABELS: the 🔥 Hot / ✨ New badges (and ⭐ Featured on admin pages).
     Usage: <x-place-labels :destination="$destination" />
            <x-place-labels :destination="$destination" featured />   (admin: also shows Featured)
     The emoji is decoration (aria-hidden); the word next to it carries the meaning.
     Prints nothing when the place has no labels. --}}
@props(['destination', 'featured' => false])

@if ($featured && $destination->is_featured)
    <span class="badge badge-featured"><span aria-hidden="true">⭐</span> Featured</span>
@endif
@if ($destination->is_hot)
    <span class="badge badge-hot"><span aria-hidden="true">🔥</span> Hot</span>
@endif
@if ($destination->is_new)
    <span class="badge badge-new"><span aria-hidden="true">✨</span> New</span>
@endif
