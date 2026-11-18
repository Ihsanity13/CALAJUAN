{{-- FORM FIELD: a label, the input, an optional hint, and the error message
     under the field when validation fails. Used by every form.

     Usage:
       <x-input name="email" label="Email" type="email" :value="old('email')" required autofocus />
       <x-input name="description" label="Description" type="textarea" optional rows="4" />

     Anything else you add (required, autofocus, autocomplete, step...) goes onto the input itself.
     aria-invalid + aria-describedby make screen readers read out the error with the field. --}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'optional' => false,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);

    // Link the input to its hint and error text, so they're read out together
    $describedBy = collect([
        $hint ? $id . '-hint' : null,
        $hasError ? $id . '-error' : null,
    ])->filter()->implode(' ');

    $inputAttributes = $attributes->except('id')->merge([
        'id' => $id,
        'name' => $name,
        'aria-invalid' => $hasError ? 'true' : null,
        'aria-describedby' => $describedBy ?: null,
    ]);
@endphp

<div class="field">
    <label for="{{ $id }}">
        {{ $label }}
        @if ($optional)
            <span class="optional">(optional)</span>
        @endif
    </label>

    @if ($type === 'textarea')
        <textarea {{ $inputAttributes }}>{{ $value }}</textarea>
    @else
        {{-- Password fields never get their value sent back to the page --}}
        <input type="{{ $type }}" {{ $inputAttributes }}
               @if ($value !== null && $type !== 'password') value="{{ $value }}" @endif>
    @endif

    @if ($hint)
        <p class="field-hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="field-error" id="{{ $id }}-error">
            <x-icon name="alert" size="16" />
            {{ $message }}
        </p>
    @enderror
</div>
