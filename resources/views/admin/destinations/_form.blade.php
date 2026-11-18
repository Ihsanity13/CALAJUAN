{{-- SHARED FORM FIELDS for create and edit.
     old('x', $destination->x ?? default) means: if validation just failed, show what
     the user typed; otherwise show the saved value (edit) or the default (create,
     where $destination doesn't exist).
     <x-input> draws the label, the box, and the error message under it.

     Used by the admin pages AND the business owner pages. The including page can pass:
       $mode         'admin' (default) or 'owner'. Owners don't get the labels (featured/hot/new).
       $cancelUrl    where Cancel goes (default: the admin destinations list)
       $submitLabel  the button text (default: "Save destination")
       $photoNote    an extra line under the photo picker
       $pendingPhotos photos waiting for approval, shown read-only --}}
@csrf

@php
    $mode ??= 'admin';
    $isAdminForm = $mode === 'admin';
@endphp

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

@php
    // After a failed save, show exactly what was submitted. An unticked checkbox
    // sends nothing, so "missing from the old input" must mean "unticked",
    // not "use the saved value". $hasOld tells the two situations apart.
    $hasOld = session()->hasOldInput();
@endphp

{{-- INTERESTS: what the place is good for. Recommendations match these
     against the interests a visitor picks. Managed under Dashboard > Interests. --}}
@php
    $chosenInterests = $hasOld
        ? array_map('intval', (array) old('interests', []))
        : (isset($destination) ? $destination->interests->pluck('id')->all() : []);
@endphp
<fieldset class="field check-group">
    <legend class="field-label">Interests <span class="optional">(optional)</span></legend>
    <p class="field-hint">Tick everything this place is good for. Used for recommendations.</p>

    @if ($interests->isEmpty())
        <p class="muted">
            No interests yet.
            @if ($isAdminForm) <a href="{{ route('admin.interests.create') }}">Add some first</a>. @endif
        </p>
    @else
        <div class="check-chips">
            @foreach ($interests as $interest)
                <label class="check-chip">
                    <input type="checkbox" name="interests[]" value="{{ $interest->id }}"
                           @checked(in_array($interest->id, $chosenInterests, true))>
                    <span>{{ $interest->name }}</span>
                </label>
            @endforeach
        </div>
    @endif

    @if ($errors->has('interests') || $errors->has('interests.*'))
        <p class="field-error">
            <x-icon name="alert" size="16" />
            {{ $errors->first('interests') ?: $errors->first('interests.*') }}
        </p>
    @endif
</fieldset>

{{-- PRICE: the level is what the budget filter uses; the note gives the details --}}
<div class="form-row">
    <div class="field">
        <label for="price_level">Price level <span class="optional">(optional)</span></label>
        <select id="price_level" name="price_level"
                @error('price_level') aria-invalid="true" aria-describedby="price_level-error" @enderror>
            <option value="">Not set</option>
            @foreach (\App\Models\Destination::PRICE_LEVELS as $level => $price)
                <option value="{{ $level }}"
                    @selected((string) old('price_level', $destination->price_level ?? '') === (string) $level)>
                    {{ $price['symbol'] === $price['label'] ? $price['label'] : $price['symbol'] . ' · ' . $price['label'] }}
                </option>
            @endforeach
        </select>
        @error('price_level')
            <p class="field-error" id="price_level-error"><x-icon name="alert" size="16" /> {{ $message }}</p>
        @enderror
    </div>

    <x-input name="price_note" label="Price details" optional maxlength="255"
             :value="old('price_note', $destination->price_note ?? '')"
             hint="e.g. Entrance ₱50 per adult, cottages from ₱500" />
</div>

{{-- CONTACT DETAILS --}}
<div class="form-row">
    <x-input name="phone" label="Phone" type="tel" optional maxlength="30" autocomplete="off"
             :value="old('phone', $destination->phone ?? '')" hint="e.g. 0917 123 4567" />
    <x-input name="email" label="Email" type="email" optional maxlength="255" autocomplete="off"
             :value="old('email', $destination->email ?? '')" />
</div>
<div class="form-row">
    <x-input name="website" label="Website" type="url" optional maxlength="255"
             :value="old('website', $destination->website ?? '')" hint="e.g. https://example.com" />
    <x-input name="facebook" label="Facebook page" optional maxlength="255"
             :value="old('facebook', $destination->facebook ?? '')" hint="e.g. facebook.com/yourpage" />
</div>

{{-- OPENING HOURS: one row per day, Monday first. Tick the days it's open.
     No day ticked = "hours not listed". See the destination_hours migration for the rules. --}}
@php
    $savedHours = isset($destination) ? $destination->hours->keyBy('day') : collect();
@endphp
<fieldset class="field hours-fieldset" data-hours>
    <legend class="field-label">Opening hours <span class="optional">(optional)</span></legend>
    <p class="field-hint" id="hours-hint">
        Tick the days it's open. Closing earlier than opening = closes after midnight.
        Same opening and closing time = open 24 hours. Leave every day unticked if you don't know the hours.
    </p>

    <ul class="hours-list" role="list">
        @foreach (\App\Models\DestinationHour::WEEK_ORDER as $day)
            @php
                $dayName = \App\Models\DestinationHour::DAYS[$day];
                $saved = $savedHours->get($day);
                $isOpen = $hasOld ? (bool) old("hours.$day.open") : $saved !== null;
                $opensAt = old("hours.$day.opens_at", $saved?->opensAt() ?? '08:00');
                $closesAt = old("hours.$day.closes_at", $saved?->closesAt() ?? '17:00');
                $dayError = $errors->first("hours.$day.opens_at") ?: $errors->first("hours.$day.closes_at");
            @endphp
            {{-- CSS (:has) hides the time boxes while the day is unticked and shows "Closed" --}}
            <li class="hours-row" data-hours-row>
                <label class="check hours-day">
                    <input type="checkbox" name="hours[{{ $day }}][open]" value="1" @checked($isOpen) data-hours-open>
                    <span>{{ $dayName }}</span>
                </label>

                <div class="hours-times">
                    <label class="sr-only" for="hours-{{ $day }}-opens">{{ $dayName }} opens at</label>
                    <input type="time" id="hours-{{ $day }}-opens" name="hours[{{ $day }}][opens_at]"
                           value="{{ $opensAt }}" data-hours-opens
                           @if ($dayError) aria-invalid="true" aria-describedby="hours-{{ $day }}-error" @endif>
                    <span aria-hidden="true">to</span>
                    <label class="sr-only" for="hours-{{ $day }}-closes">{{ $dayName }} closes at</label>
                    <input type="time" id="hours-{{ $day }}-closes" name="hours[{{ $day }}][closes_at]"
                           value="{{ $closesAt }}" data-hours-closes
                           @if ($dayError) aria-invalid="true" aria-describedby="hours-{{ $day }}-error" @endif>
                    <span class="hours-closed-text">Closed</span>
                </div>

                @if ($dayError)
                    <p class="field-error" id="hours-{{ $day }}-error">
                        <x-icon name="alert" size="16" /> {{ $dayName }}: {{ $dayError }}
                    </p>
                @endif
            </li>
        @endforeach
    </ul>

    {{-- Shortcut: copy Monday's hours to the other days (public/js/hours-form.js) --}}
    <button type="button" class="btn btn-ghost btn-sm" data-hours-copy hidden>
        <x-icon name="check" size="16" /> Copy Monday's hours to every day
    </button>
    <p class="sr-only" aria-live="polite" data-hours-status></p>
</fieldset>

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
    <script src="{{ asset('js/hours-form.js') }}"></script>
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

    {{-- Photos waiting for approval (owner pages): shown, but nothing to click --}}
    @if (isset($pendingPhotos) && $pendingPhotos->isNotEmpty())
        <span class="field-label">New photos waiting for approval</span>
        <ul class="photo-grid" role="list">
            @foreach ($pendingPhotos as $photo)
                <li class="photo-item is-pending">
                    <div class="photo-thumb">
                        <img src="{{ $photo->url }}" alt="New photo {{ $loop->iteration }}, waiting for approval"
                             loading="lazy" width="320" height="240">
                        <span class="badge badge-pending photo-badge">Waiting</span>
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
            ({{ $photoCount }} saved, {{ max(0, 10 - $photoCount - (isset($pendingPhotos) ? $pendingPhotos->count() : 0)) }} left)@endif.
        @if ($photoCount === 0)
            The first photo becomes the primary photo shown on cards.
        @endif
        @isset($photoNote)
            <br>{{ $photoNote }}
        @endisset
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

{{-- LABELS. @checked(...) adds the "checked" attribute when the condition is true.
     An unticked checkbox sends nothing; DestinationRequest handles that,
     and $hasOld (above) keeps an unticked box unticked after a failed save. --}}
@if ($isAdminForm)
@php
    $label = fn (string $field) => $hasOld ? (bool) old($field) : (bool) ($destination->$field ?? false);
@endphp
<fieldset class="field check-group">
    <legend class="field-label">Labels</legend>

    <label class="check">
        <input type="checkbox" name="is_featured" value="1" @checked($label('is_featured'))>
        <span class="check-text">
            <span aria-hidden="true">⭐</span> Featured
            <small>Shown on the home page.</small>
        </span>
    </label>

    <label class="check">
        <input type="checkbox" name="is_hot" value="1" @checked($label('is_hot'))>
        <span class="check-text">
            <span aria-hidden="true">🔥</span> Hot
            <small>Popular right now. Shows a "Hot" badge on the place.</small>
        </span>
    </label>

    <label class="check">
        <input type="checkbox" name="is_new" value="1" @checked($label('is_new'))>
        <span class="check-text">
            <span aria-hidden="true">✨</span> New
            <small>Recently opened or just added. Shows a "New" badge on the place.</small>
        </span>
    </label>
</fieldset>
@endif

<div class="form-actions">
    <button type="submit" class="btn">
        <x-icon name="check" size="18" />
        {{ $submitLabel ?? 'Save destination' }}
    </button>
    <a class="btn btn-ghost" href="{{ $cancelUrl ?? route('admin.destinations.index') }}">Cancel</a>
</div>
