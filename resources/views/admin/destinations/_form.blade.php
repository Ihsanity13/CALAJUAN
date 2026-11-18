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

<x-input name="address" label="Address" optional :value="old('address', $destination->address ?? '')" />

{{-- Two boxes side by side on wider screens (.form-row).
     step="any" lets the number box accept decimals like 14.1467 --}}
<div class="form-row">
    <x-input name="latitude" label="Latitude" type="number" step="any" required
             hint="Calauan is about 14.14"
             :value="old('latitude', $destination->latitude ?? '')" />

    <x-input name="longitude" label="Longitude" type="number" step="any" required
             hint="Calauan is about 121.31"
             :value="old('longitude', $destination->longitude ?? '')" />
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
