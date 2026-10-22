@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <h1>Create an account</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
        @error('name')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">Password (at least 8 characters)</label>
        <input type="password" id="password" name="password" required>
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        {{-- The field must be named password_confirmation, because the
             'confirmed' rule in the controller looks for that exact name. --}}
        <label for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>

        <button type="submit" class="btn">Register</button>
    </form>

    <p>Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection