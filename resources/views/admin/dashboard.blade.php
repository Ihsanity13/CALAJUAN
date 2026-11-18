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

    {{-- QUICK NUMBERS. $counts comes from one query in Admin\DashboardController. --}}
    <ul class="stat-row" role="list">
        <li class="card stat">
            <span class="stat-value">{{ $counts->total }}</span>
            <span class="stat-label"><x-icon name="map-pin" size="16" /> Places</span>
        </li>
        <li class="card stat">
            <span class="stat-value">{{ $counts->featured }}</span>
            <span class="stat-label"><span aria-hidden="true">⭐</span> Featured</span>
        </li>
        <li class="card stat stat-hot">
            <span class="stat-value">{{ $counts->hot }}</span>
            <span class="stat-label"><span aria-hidden="true">🔥</span> Hot</span>
        </li>
        <li class="card stat stat-new">
            <span class="stat-value">{{ $counts->new }}</span>
            <span class="stat-label"><span aria-hidden="true">✨</span> New</span>
        </li>
    </ul>

    {{-- Each tile is one big link (a.card), so the whole tile is clickable --}}
    <div class="admin-grid">
        <a class="card admin-tile" href="{{ route('admin.destinations.index') }}">
            <span class="tile-icon"><x-icon name="map-pin" /></span>
            <h2>Destinations</h2>
            <p class="muted">Add places, edit their details, hours and contacts, and label them ⭐ Featured, 🔥 Hot or ✨ New.</p>
            <span class="tile-link">Manage destinations <x-icon name="arrow-right" size="18" /></span>
        </a>

        <a class="card admin-tile" href="{{ route('admin.categories.index') }}">
            <span class="tile-icon"><x-icon name="tag" /></span>
            <h2>Categories <span class="heading-count">({{ $categoryCount }})</span></h2>
            <p class="muted">What a place is: the groups destinations belong to.</p>
            <span class="tile-link">Manage categories <x-icon name="arrow-right" size="18" /></span>
        </a>

        <a class="card admin-tile" href="{{ route('admin.interests.index') }}">
            <span class="tile-icon"><x-icon name="heart" /></span>
            <h2>Interests <span class="heading-count">({{ $interestCount }})</span></h2>
            <p class="muted">What a place is good for (Nature, Food, Adventure...). Used for recommendations.</p>
            <span class="tile-link">Manage interests <x-icon name="arrow-right" size="18" /></span>
        </a>
    </div>
@endsection
