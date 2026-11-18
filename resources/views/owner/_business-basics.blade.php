{{-- THE BASICS about a business: name, type, phone.
     Asked at sign-up, and shown again (to correct) on the verification page.
     Needs $categories (the types of business). $verification (optional) fills them in.
     Rules: OwnerVerificationRequest::basicRules(). --}}
@php
    $verification ??= null;
    $categoryError = $errors->first('category_id');
@endphp

<x-input name="business_name" label="Business name" maxlength="255"
         :value="old('business_name', $verification?->business_name)" />

<div class="field">
    <label for="category_id">Type of business</label>
    <select id="category_id" name="category_id"
            @if ($categoryError) aria-invalid="true" aria-describedby="category_id-error" @endif>
        <option value="">Choose one</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}"
                @selected((string) old('category_id', $verification?->category_id) === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @if ($categoryError)
        <p class="field-error" id="category_id-error"><x-icon name="alert" size="16" /> {{ $categoryError }}</p>
    @endif
</div>

<x-input name="business_phone" label="Business phone" type="tel" maxlength="30"
         :value="old('business_phone', $verification?->business_phone)" />
