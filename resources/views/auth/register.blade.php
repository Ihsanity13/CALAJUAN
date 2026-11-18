@extends('layouts.app')

@section('title', 'Create an account')

@section('content')
    <div class="auth-head">
        <span class="brand-mark brand-mark-lg" aria-hidden="true"></span>
        <h1>Create an account</h1>
        <p class="muted">Save places you like and get suggestions around Calauan.</p>
    </div>

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

        <button type="submit" class="btn btn-gold btn-block auth-submit">Create account</button>
    </form>

    <p class="auth-foot">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection
