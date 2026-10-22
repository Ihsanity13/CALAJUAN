{{-- SHARED FORM FIELDS for create and edit.
     old('x', $destination->x ?? default) means: if validation just failed, show what
     the user typed; otherwise show the saved value (edit) or the default (create,
     where $destination doesn't exist). --}}
@csrf

<label for="category_id">Category</label>
<select id="category_id" name="category_id" required>
    <option value="">-- Choose a category --</option>
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
    <div class="error">{{ $message }}</div>
@enderror

<label for="name">Name</label>
<input type="text" id="name" name="name" value="{{ old('name', $destination->name ?? '') }}" required>
@error('name')
    <div class="error">{{ $message }}</div>
@enderror

<label for="description">Description (optional)</label>
<textarea id="description" name="description" rows="4">{{ old('description', $destination->description ?? '') }}</textarea>
@error('description')
    <div class="error">{{ $message }}</div>
@enderror

<label for="address">Address (optional)</label>
<input type="text" id="address" name="address" value="{{ old('address', $destination->address ?? '') }}">
@error('address')
    <div class="error">{{ $message }}</div>
@enderror

{{-- step="any" lets the number box accept decimals like 14.1467 --}}
<label for="latitude">Latitude (Calauan is about 14.14)</label>
<input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude', $destination->latitude ?? '') }}" required>
@error('latitude')
    <div class="error">{{ $message }}</div>
@enderror

<label for="longitude">Longitude (Calauan is about 121.31)</label>
<input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude', $destination->longitude ?? '') }}" required>
@error('longitude')
    <div class="error">{{ $message }}</div>
@enderror

{{-- @checked(...) adds the "checked" attribute when the condition is true.
     An unticked checkbox sends nothing; DestinationRequest handles that. --}}
<label>
    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $destination->is_featured ?? false))>
    Featured
</label>

<label>
    <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $destination->is_verified ?? false))>
    Verified (the tourism information has been checked)
</label>

<button type="submit" class="btn">Save</button>
<a href="{{ route('admin.destinations.index') }}">Cancel</a>