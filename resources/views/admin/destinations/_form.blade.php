{{-- SHARED FORM FIELDS for create and edit.
     old('x', $destination->x ?? default) means: if validation just failed, show what
     the user typed; otherwise show the saved value (edit) or the default (create,
     where $destination doesn't exist).
     <x-input> draws the label, the box, and the error message under it. --}}
@csrf

{{-- The category dropdown is written out by hand (x-input only does text boxes),
     but it follows the same pattern: label, control, error underneath. --}}
<div class="field">
    <label for="category_id">Category</label>
    <select id="category_id" name="category_id" required
            @error('category_id') aria-invalid="true" aria-describedby="category_id-error" @enderror>
        <option value="">Choose a category</option>
        @foreach ($categories as $category)
            {{-- @selected(...) adds the "selected" attribute when the condition is true,
                 so the dropdown shows the current choice. --}}
            <option value="{{ $category->id }}"
                @selected(old('category_id', $destination->category_id ?? '') == $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <p class="field-error" id="category_id-error">
            <x-icon name="alert" size="16" />
            {{ $message }}
        </p>
    @enderror
</div>

<x-input name="name" label="Name" :value="old('name', $destination->name ?? '')" required />

<x-input name="description" label="Description" type="textarea" optional rows="4"
         :value="old('description', $destination->description ?? '')" />

{{-- LOCATION: a Leaflet map instead of typing coordinates.
     public/js/map-picker.js does the work: clicking the map drops a pin,
     and the pin's position is copied into the two hidden inputs below,
     which are what the form actually sends to Laravel.
     The server still validates them (DestinationRequest). --}}
@php
    $lat = old('latitude', $destination->latitude ?? '');
    $lng = old('longitude', $destination->longitude ?? '');
    // Both fields share one message ("Drop a pin..."), so show it once
    $locationError = $errors->first('latitude') ?: $errors->first('longitude');
@endphp

<div class="field" data-map-picker>
    <span class="field-label" id="location-label">Location on the map</span>
    <p class="field-hint" id="location-hint">
        Click the map to drop a pin, then drag it to adjust.
        Keyboard: arrow keys move the map, Enter drops the pin in the center.
    </p>

    <input type="hidden" name="latitude" value="{{ $lat }}" data-lat>
    <input type="hidden" name="longitude" value="{{ $lng }}" data-lng>

    {{-- The map draws itself in here. Default center: Calauan, Laguna. --}}
    <div class="map-picker {{ $locationError ? 'is-invalid' : '' }}"
         data-map
         data-default-lat="14.1467" data-default-lng="121.3167" data-default-zoom="14"
         role="application"
         aria-labelledby="location-label"
         aria-describedby="location-hint location-coords{{ $locationError ? ' location-error' : '' }}"
         tabindex="0"></div>

    <div class="map-bar">
        {{-- aria-live: screen readers announce the new coordinates when the pin moves --}}
        <p class="map-coords" id="location-coords" aria-live="polite" data-coords>
            @if ($lat !== '' && $lng !== '')
                Pin at {{ number_format((float) $lat, 5) }}, {{ number_format((float) $lng, 5) }}
            @else
                No pin yet
            @endif
        </p>

        <button type="button" class="btn btn-outline btn-sm" data-locate>
            <x-icon name="map-pin" size="16" />
            Use my location
        </button>
    </div>

    {{-- Error from the server, or from map-picker.js when you submit without a pin --}}
    <p class="field-error" id="location-error" data-location-error @unless ($locationError) hidden @endunless>
        <x-icon name="alert" size="16" />
        <span data-location-error-text>{{ $locationError ?: 'Drop a pin on the map to set the location.' }}</span>
    </p>
</div>

{{-- ADDRESS: below the map, because map-picker.js fills it in from the pin
     (it never overwrites an address you typed yourself; it offers a button instead). --}}
<x-input name="address" label="Address" optional :value="old('address', $destination->address ?? '')"
         hint="Filled in from the pin. You can edit it." maxlength="255" />

{{-- Messages from the address lookup ("Looking up the address…", "Use this address") --}}
<div class="address-lookup" data-address-lookup aria-live="polite" hidden>
    <p class="address-lookup-text" data-address-lookup-text></p>
    <button type="button" class="btn btn-ghost btn-sm" data-address-use hidden>Use this address</button>
</div>

{{-- Leaflet comes from its official CDN (no npm). integrity = a fingerprint of the
     file, so the browser refuses it if anyone ever changes it on the CDN. --}}
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/map-picker.js') }}"></script>
@endpush

{{-- PHOTOS: pick several files at once (multiple). name="photos[]" sends them as a list.
     Errors can be about the list ("photos") or about one file ("photos.0", "photos.1"...),
     so we collect them all and show each different message once. --}}
@php
    $photoErrors = collect($errors->get('photos'))
        ->merge(collect($errors->get('photos.*'))->flatten())
        ->unique()
        ->values();
    // When editing, load the saved photos once (primary first, see images() in Destination.php)
    $savedPhotos = isset($destination) ? $destination->images : collect();
    $photoCount = $savedPhotos->count();
@endphp

<div class="field" id="photos-section">
    {{-- SAVED PHOTOS (edit page only). The buttons use form="..." to submit the small
         forms printed AFTER the main form in edit.blade.php, because HTML doesn't
         allow a form inside another form. --}}
    @if ($photoCount > 0)
        <span class="field-label">Saved photos</span>
        <p class="field-hint">Photo buttons take effect right away. Save other changes on this page first.</p>
        <ul class="photo-grid" role="list">
            @foreach ($savedPhotos as $photo)
                <li class="photo-item {{ $photo->is_primary ? 'is-primary' : '' }}">
                    <div class="photo-thumb">
                        <img src="{{ $photo->url }}"
                             alt="Photo {{ $loop->iteration }} of {{ $destination->name }}"
                             loading="lazy" width="320" height="240">
                        @if ($photo->is_primary)
                            <span class="badge badge-verified photo-badge">
                                <x-icon name="star" size="14" fill="currentColor" /> Primary
                            </span>
                        @endif
                    </div>

                    <div class="photo-actions">
                        @unless ($photo->is_primary)
                            <button type="submit" class="btn btn-ghost btn-sm" form="photo-primary-{{ $photo->id }}"
                                    aria-label="Make photo {{ $loop->iteration }} the primary photo">
                                <x-icon name="star" size="16" /> Make primary
                            </button>
                        @endunless
                        <button type="submit" class="btn btn-ghost btn-danger btn-sm" form="photo-delete-{{ $photo->id }}"
                                aria-label="Delete photo {{ $loop->iteration }}">
                            <x-icon name="trash" size="16" /> Delete
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <label for="photos">
        {{ $photoCount > 0 ? 'Add more photos' : 'Photos' }} <span class="optional">(optional)</span>
    </label>
    <input type="file" id="photos" name="photos[]" multiple
           accept="image/jpeg,image/png,image/webp"
           aria-describedby="photos-hint{{ $photoErrors->isNotEmpty() ? ' photos-error' : '' }}"
           @if ($photoErrors->isNotEmpty()) aria-invalid="true" @endif>
    <p class="field-hint" id="photos-hint">
        JPG, PNG or WebP, up to 4 MB each. Up to 10 photos per place
        @if ($photoCount > 0)
            ({{ $photoCount }} saved, {{ max(0, 10 - $photoCount) }} left)@endif.
        @if ($photoCount === 0)
            The first photo becomes the primary photo shown on cards.
        @endif
    </p>
    @if ($photoErrors->isNotEmpty())
        <div id="photos-error">
            @foreach ($photoErrors as $message)
                <p class="field-error">
                    <x-icon name="alert" size="16" />
                    {{ $message }}
                </p>
            @endforeach
        </div>
    @endif
</div>

{{-- @checked(...) adds the "checked" attribute when the condition is true.
     An unticked checkbox sends nothing; DestinationRequest handles that. --}}
<fieldset class="field check-group">
    <legend class="field-label">Visibility</legend>

    <label class="check">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $destination->is_featured ?? false))>
        <span class="check-text">
            Featured
            <small>Shown on the home page (only if it's also verified).</small>
        </span>
    </label>

    <label class="check">
        <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $destination->is_verified ?? false))>
        <span class="check-text">
            Verified
            <small>The tourism information has been checked.</small>
        </span>
    </label>
</fieldset>

<div class="form-actions">
    <button type="submit" class="btn">
        <x-icon name="check" size="18" />
        Save destination
    </button>
    <a class="btn btn-ghost" href="{{ route('admin.destinations.index') }}">Cancel</a>
</div>
