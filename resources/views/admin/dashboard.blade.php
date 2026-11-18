@extends('layouts.app')

@section('title', 'Admin dashboard')
@section('main_class', 'wide')

@section('content')
    <div class="page-head">
        <div>
            <h1>Admin dashboard</h1>
            <p class="muted">Signed in as {{ auth()->user()->name }}. What would you like to manage?</p>
        </div>
        <a class="btn" href="{{ route('admin.destinations.create') }}">
            <x-icon name="plus" size="18" />
            New destination
        </a>
    </div>

    {{-- Each tile is one big link (a.card), so the whole tile is clickable --}}
    <div class="admin-grid">
        <a class="card admin-tile" href="{{ route('admin.destinations.index') }}">
            <span class="tile-icon"><x-icon name="map-pin" /></span>
            <h2>Destinations</h2>
            <p class="muted">Add places, edit their details, and mark them as featured or verified.</p>
            <span class="tile-link">Manage destinations <x-icon name="arrow-right" size="18" /></span>
        </a>

        <a class="card admin-tile" href="{{ route('admin.categories.index') }}">
            <span class="tile-icon"><x-icon name="tag" /></span>
            <h2>Categories</h2>
            <p class="muted">Add, rename, or remove the groups that destinations belong to.</p>
            <span class="tile-link">Manage categories <x-icon name="arrow-right" size="18" /></span>
        </a>
    </div>
@endsection
