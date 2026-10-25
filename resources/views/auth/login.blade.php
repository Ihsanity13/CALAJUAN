@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <style>
        .btn {
            background: #ffd83d;
            color: #14260a;
            border: 3px solid #ffd83d;
        }
        .btn:hover {
            background: #ffe27a;
            border-color: #ffe27a;
        }
    </style>

    <h1>Log in</h1>
    ...

    {{-- The form sends a POST request to the 'login' route. --}}
    <form method="POST" action="{{ route('login') }}">
        {{-- @csrf: the security token Laravel requires on every POST form. --}}
        @csrf

        <label for="email">Email</label>
        {{-- old('email') refills the field with what the user typed
             if the form comes back with errors. --}}
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        {{-- @error shows the validation message for this field, if any. --}}
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <label>
            <input type="checkbox" name="remember"> Remember me
        </label>

        <button type="submit" class="btn">Log in</button>
    </form>

    <p>No account yet? <a href="{{ route('register') }}">Register</a></p>
@endsection