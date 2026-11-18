@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="auth-head">
        <span class="brand-mark brand-mark-lg" aria-hidden="true"></span>
        <h1>Welcome back</h1>
        <p class="muted">Log in to see your saved places.</p>
    </div>

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
