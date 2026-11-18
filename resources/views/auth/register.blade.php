@extends('layouts.app')

@section('title', 'Create an account')

@section('content')
    <div class="auth-head">
        {{-- alt="": decoration here, the heading below says what the page is --}}
        <img class="brand-logo brand-logo-lg" src="{{ asset('images/logo-lg.png') }}" alt="" width="720" height="317">
        <h1>Create an account</h1>
        <p class="muted">Save places you like and get suggestions around Calauan.</p>
    </div>

    {{-- Sent here from a place they clicked on the home page --}}
    @if (session()->has('url.intended'))
        <x-alert type="info" class="form-notice">
            Create an account to see this place. We'll take you right to it.
        </x-alert>
    @endif

    {{-- novalidate: let Laravel check the fields, so errors show in our own style under each field. --}}
    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <x-input name="name" label="Name" :value="old('name')"
                 autocomplete="name" required autofocus />

        <x-input name="email" label="Email" type="email" :value="old('email')"
                 autocomplete="email" required />

        <x-input name="password" label="Password" type="password"
                 hint="At least 8 characters." autocomplete="new-password" required />

        {{-- The field must be named password_confirmation, because the
             'confirmed' rule in the controller looks for that exact name.
             A mismatch is reported under the Password field above. --}}
        <x-input name="password_confirmation" label="Confirm password" type="password"
                 autocomplete="new-password" required />

        {{-- ACCOUNT TYPE: two radio buttons styled as cards. "visitor" is ticked by default. --}}
        <fieldset class="field account-type">
            <legend class="field-label">I'm signing up as</legend>
            <div class="choice-cards">
                <label class="choice-card">
                    <input type="radio" name="account_type" value="visitor" @checked(old('account_type', 'visitor') !== 'owner')>
                    <span class="choice-text">
                        <strong>Visitor</strong>
                        <small>A tourist or resident exploring Calauan</small>
                    </span>
                </label>
                <label class="choice-card">
                    <input type="radio" name="account_type" value="owner" @checked(old('account_type') === 'owner')>
                    <span class="choice-text">
                        <strong>Business owner</strong>
                        <small>I want to list and manage my place</small>
                    </span>
                </label>
            </div>
            @error('account_type')
                <p class="field-error"><x-icon name="alert" size="16" /> {{ $message }}</p>
            @enderror
        </fieldset>

        {{-- BUSINESS DETAILS: only shown while "Business owner" is ticked (CSS :has, no JavaScript).
             The server ignores these fields for visitors (exclude_unless in RegisterRequest). --}}
        <fieldset class="owner-fields">
            <legend class="field-label">Your business</legend>
            <p class="field-hint">
                Just the basics for now. After you sign up, we'll ask for your business permit
                so we can verify your business before you add places.
            </p>
            @include('owner._business-basics')
        </fieldset>

        <button type="submit" class="btn btn-gold btn-block auth-submit">Create account</button>
    </form>

    <p class="auth-foot">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
