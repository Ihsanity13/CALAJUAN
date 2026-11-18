@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="auth-head">
        {{-- alt="": decoration here, the heading below says what the page is --}}
        <img class="brand-logo brand-logo-lg" src="{{ asset('images/logo-lg.png') }}" alt="" width="720" height="317">
        <h1>Welcome back</h1>
        <p class="muted">Log in to see your saved places.</p>
    </div>

    {{-- Sent here from a page that needs an account (e.g. a place they clicked on the home page) --}}
    @if (session()->has('url.intended'))
        <x-alert type="info" class="form-notice">
            Log in or <a href="{{ route('register') }}">create an account</a> to see this place.
            We'll take you right to it.
        </x-alert>
    @endif

    {{-- The form sends a POST request to the 'login' route.
         novalidate: let Laravel check the fields, so errors show in our own style under each field. --}}
    <form method="POST" action="{{ route('login') }}" novalidate>
        {{-- @csrf: the security token Laravel requires on every POST form. --}}
        @csrf

        {{-- old('email') refills the field with what the user typed
             if the form comes back with errors. --}}
        <x-input name="email" label="Email" type="email" :value="old('email')"
                 autocomplete="email" required autofocus />

        <x-input name="password" label="Password" type="password"
                 autocomplete="current-password" required />

        <label class="check">
            <input type="checkbox" name="remember" @checked(old('remember'))>
            <span>Remember me</span>
        </label>

        <button type="submit" class="btn btn-gold btn-block auth-submit">Log in</button>
    </form>

    <p class="auth-foot">No account yet? <a href="{{ route('register') }}">Create one</a></p>
@endsection
