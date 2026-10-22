@extends('layouts.app')

@section('title', 'Admin dashboard')
@section('main_class', 'wide')

@section('content')
    <h1>Admin dashboard</h1>
    <p class="muted">Signed in as {{ auth()->user()->name }}. What would you like to manage?</p>

    <div class="places">
        <a class="place" href="{{ route('admin.categories.index') }}">
            <div>
                <h3>Categories</h3>
                <p class="muted">Add, rename, or remove the groups that destinations belong to.</p>
            </div>
            <span class="tag">Manage</span>
        </a>

        <a class="place" href="{{ route('admin.destinations.index') }}">
            <div>
                <h3>Destinations</h3>
                <p class="muted">Add places, edit their details, and mark them as featured or verified.</p>
            </div>
            <span class="tag">Manage</span>
        </a>
    </div>
@endsection