@extends('layouts.app')

@section('title', 'My profile')
@section('main_class', 'wide')

{{-- A visitor's profile: their saved places ($saved, from SavedPlaceController) --}}
@section('content')
    <div class="page-head">
        <div>
            <h1>Hi, {{ auth()->user()->name }}</h1>
            <p class="muted">{{ auth()->user()->email }}</p>
        </div>
    </div>

    {{-- id="saved": the "Saved" links in the menu jump straight here --}}
    <section id="saved" aria-labelledby="saved-heading">
        <h2 id="saved-heading">
            Saved places
            @if ($saved->isNotEmpty())
                <span class="heading-count">({{ $saved->count() }})</span>
            @endif
        </h2>

        @if ($saved->isEmpty())
            <div class="empty-state">
                <x-icon name="heart" size="32" />
                <h3>No saved places yet</h3>
                <p>Tap <strong>Save</strong> on any place to keep it here for later.</p>
                <a class="btn" href="{{ route('home') }}">
                    <x-icon name="compass" size="18" /> Explore places
                </a>
            </div>
        @else
            <div class="dest-grid">
                @foreach ($saved as $destination)
                    <x-destination-card :destination="$destination" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
