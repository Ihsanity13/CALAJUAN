{{-- SHARED FORM FIELDS for create and edit (same pattern as the category form). --}}
@csrf

<x-input name="name" label="Name" :value="old('name', $interest->name ?? '')" required autofocus maxlength="50"
         hint="What a place is good for, e.g. Nature, Food, Adventure, Relaxation. Visitors see these as Experiences." />

{{-- COLOR: radio buttons shown as colored pills, so you see exactly how it will look --}}
@php $chosenColor = old('color', $interest->color ?? 'gray'); @endphp
<fieldset class="field color-choices">
    <legend class="field-label">Color</legend>
    <div class="color-options">
        @foreach (\App\Models\Interest::COLORS as $value => $label)
            <label class="color-option">
                <input type="radio" name="color" value="{{ $value }}" @checked($chosenColor === $value)>
                <span class="interest-pill interest-{{ $value }}">{{ $label }}</span>
            </label>
        @endforeach
    </div>
    @error('color')
        <p class="field-error"><x-icon name="alert" size="16" /> {{ $message }}</p>
    @enderror
</fieldset>

<div class="form-actions">
    <button type="submit" class="btn">
        <x-icon name="check" size="18" />
        Save interest
    </button>
    <a class="btn btn-ghost" href="{{ route('admin.interests.index') }}">Cancel</a>
</div>
