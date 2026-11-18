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

    {{-- Something is waiting for a decision: say so first --}}
    @if ($approvalCount > 0)
        <x-alert type="warning" class="form-notice">
            <strong>{{ $approvalCount }} {{ \Illuminate\Support\Str::plural('submission', $approvalCount) }} from business owners {{ $approvalCount === 1 ? 'is' : 'are' }} waiting for approval.</strong>
            <a href="{{ route('admin.approvals.index') }}">Review now</a>
        </x-alert>
    @endif

    {{-- QUICK NUMBERS (live places only). $counts comes from one query in Admin\DashboardController.
         Each one links to the destinations list, filtered to those places. --}}
    <ul class="stat-row" role="list">
        <li>
            <a class="card stat stat-link" href="{{ route('admin.destinations.index', ['show' => 'live']) }}">
                <span class="stat-value">{{ $counts->total }}</span>
                <span class="stat-label"><x-icon name="map-pin" size="16" /> Live places <x-icon name="arrow-right" size="14" class="stat-arrow" /></span>
            </a>
        </li>
        <li>
            <a class="card stat stat-link" href="{{ route('admin.destinations.index', ['show' => 'featured']) }}">
                <span class="stat-value">{{ $counts->featured }}</span>
                <span class="stat-label"><span aria-hidden="true">⭐</span> Featured <x-icon name="arrow-right" size="14" class="stat-arrow" /></span>
            </a>
        </li>
        <li>
            <a class="card stat stat-link stat-hot" href="{{ route('admin.destinations.index', ['show' => 'hot']) }}">
                <span class="stat-value">{{ $counts->hot }}</span>
                <span class="stat-label"><span aria-hidden="true">🔥</span> Hot <x-icon name="arrow-right" size="14" class="stat-arrow" /></span>
            </a>
        </li>
        <li>
            <a class="card stat stat-link stat-new" href="{{ route('admin.destinations.index', ['show' => 'new']) }}">
                <span class="stat-value">{{ $counts->new }}</span>
                <span class="stat-label"><span aria-hidden="true">✨</span> New <x-icon name="arrow-right" size="14" class="stat-arrow" /></span>
            </a>
        </li>
    </ul>

    {{-- Each tile is one big link (a.card), so the whole tile is clickable --}}
    <div class="admin-grid">
        <a class="card admin-tile" href="{{ route('admin.approvals.index') }}">
            <span class="tile-icon"><x-icon name="clock" /></span>
            <h2>Approvals <span class="heading-count">({{ $approvalCount }})</span></h2>
            <p class="muted">Verify new business owners, and approve or reject the places and changes they send.</p>
            <span class="tile-link">Review submissions <x-icon name="arrow-right" size="18" /></span>
        </a>

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
