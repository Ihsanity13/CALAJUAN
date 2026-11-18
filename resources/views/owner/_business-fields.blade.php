{{-- STEP 2 of becoming a business owner: the basics again (to correct them) plus the
     permit details. Used on the owner's verification page.
     Needs $categories. $verification (optional): the earlier details, to fill the fields in.
     The rules are in OwnerVerificationRequest::rules(). --}}
@php
    $verification ??= null;
    $selectField = function (string $name, string $label, array $options, ?string $saved) use ($errors) {
        return compact('name', 'label', 'options', 'saved') + ['error' => $errors->first($name)];
    };
    $selects = [
        $selectField('applicant_role', 'Your role in the business', \App\Models\OwnerVerification::APPLICANT_ROLES, $verification?->applicant_role),
        $selectField('registration_type', 'Business registration', \App\Models\OwnerVerification::REGISTRATION_TYPES, $verification?->registration_type),
    ];
@endphp

@include('owner._business-basics', ['verification' => $verification])

<x-input name="business_address" label="Business address" maxlength="255"
         :value="old('business_address', $verification?->business_address)" />

@foreach ($selects as $select)
    <div class="field">
        <label for="{{ $select['name'] }}">{{ $select['label'] }}</label>
        <select id="{{ $select['name'] }}" name="{{ $select['name'] }}"
                @if ($select['error']) aria-invalid="true" aria-describedby="{{ $select['name'] }}-error" @endif>
            <option value="">Choose one</option>
            @foreach ($select['options'] as $value => $text)
                <option value="{{ $value }}" @selected(old($select['name'], $select['saved']) === $value)>{{ $text }}</option>
            @endforeach
        </select>
        @if ($select['error'])
            <p class="field-error" id="{{ $select['name'] }}-error"><x-icon name="alert" size="16" /> {{ $select['error'] }}</p>
        @endif
    </div>
@endforeach

<x-input name="registration_number" label="Registration or permit number" maxlength="50"
         :value="old('registration_number', $verification?->registration_number)"
         hint="The number on the document you upload below." />

{{-- The document itself. Private: only the Calajuan admins can open it. --}}
<div class="field">
    <label for="permit_document">
        Photo or scan of the permit / certificate
        @if ($verification?->document_path) <span class="optional">(optional: we still have your last upload)</span> @endif
    </label>
    <input type="file" id="permit_document" name="permit_document" accept="image/jpeg,image/png,image/webp,application/pdf"
           aria-describedby="permit_document-hint{{ $errors->has('permit_document') ? ' permit_document-error' : '' }}"
           @error('permit_document') aria-invalid="true" @enderror>
    <p class="field-hint" id="permit_document-hint">
        JPG, PNG, WebP or PDF, up to 5 MB. Only the Calajuan team can see it; it's never shown publicly.
    </p>
    @error('permit_document')
        <p class="field-error" id="permit_document-error"><x-icon name="alert" size="16" /> {{ $message }}</p>
    @enderror
</div>

<div class="field">
    <label class="check">
        <input type="checkbox" name="confirm_legit" value="1" @checked(old('confirm_legit'))
               @error('confirm_legit') aria-invalid="true" aria-describedby="confirm_legit-error" @enderror>
        <span class="check-text">
            I am the owner or an authorized representative of this business, and the information above is true.
            <small>Accounts with false information are removed.</small>
        </span>
    </label>
    @error('confirm_legit')
        <p class="field-error" id="confirm_legit-error"><x-icon name="alert" size="16" /> {{ $message }}</p>
    @enderror
</div>
