@extends('layouts.app')

@section('title', 'My places')
@section('main_class', 'wide')

@section('content')
    <div class="page-head">
        <div>
            <h1>My places</h1>
            <p class="muted">
                Add your business and keep its details up to date.
                The Calajuan team checks every new place and change before visitors see it.
            </p>
        </div>
        <a class="btn" href="{{ route('owner.places.create') }}">
            <x-icon name="plus" size="18" />
            Add a place
        </a>
    </div>

    @if ($places->isEmpty())
        <div class="empty-state">
            <x-icon name="map-pin" size="32" />
            <h3>No places yet</h3>
            <p>Add your café, restaurant, resort or other business so visitors can find it.</p>
            <a class="btn" href="{{ route('owner.places.create') }}">
                <x-icon name="plus" size="18" /> Add your first place
            </a>
        </div>
    @else
        <ul class="owner-list" role="list">
            @foreach ($places as $place)
                @php
                    // The latest change request, if it was rejected and nothing newer is waiting
                    $rejectedChange = ! $place->pendingChange
                        && $place->latestChange?->status === \App\Models\DestinationChange::STATUS_REJECTED
                            ? $place->latestChange
                            : null;
                @endphp
                <li class="card owner-place">
                    <div class="owner-thumb">
                        @if ($place->primaryImage)
                            <img src="{{ $place->primaryImage->url }}" alt="" loading="lazy" width="160" height="120">
                        @else
                            <div class="img-placeholder" aria-hidden="true"></div>
                        @endif
                    </div>

                    <div class="owner-body">
                        <h2 class="owner-name">{{ $place->name }}</h2>
                        <div class="dest-meta">
                            <span class="tag">{{ $place->category->name }}</span>
                            <x-status-badge :status="$place->status" />
                            @if ($place->pendingChange)
                                <span class="badge badge-pending"><x-icon name="clock" size="14" /> Changes waiting for approval</span>
                            @endif
                        </div>

                        {{-- Why it was rejected, so the owner knows what to fix --}}
                        @if ($place->status === \App\Models\Destination::STATUS_REJECTED && $place->rejection_reason)
                            <p class="owner-reason">
                                <strong>Reason:</strong> {{ $place->rejection_reason }}
                                <br><span class="muted">Edit the place to fix it, then save to send it again.</span>
                            </p>
                        @elseif ($rejectedChange)
                            <p class="owner-reason">
                                <strong>Your last changes were not approved:</strong> {{ $rejectedChange->rejection_reason }}
                            </p>
                        @endif
                    </div>

                    <div class="owner-actions">
                        <a class="btn btn-outline btn-sm" href="{{ route('owner.places.edit', $place) }}"
                           aria-label="Edit {{ $place->name }}">
                            <x-icon name="pencil" size="16" /> Edit
                        </a>
                        <a class="btn btn-ghost btn-sm" href="{{ route('destinations.show', $place) }}"
                           aria-label="{{ $place->isApproved() ? 'View' : 'Preview' }} {{ $place->name }}">
                            {{ $place->isApproved() ? 'View' : 'Preview' }}
                        </a>
                        @unless ($place->isApproved())
                            @php $confirm = 'Delete your submission "' . $place->name . '"? This cannot be undone.'; @endphp
                            <form method="POST" action="{{ route('owner.places.destroy', $place) }}"
                                  onsubmit="return confirm(@js($confirm))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-danger btn-sm"
                                        aria-label="Delete {{ $place->name }}">
                                    <x-icon name="trash" size="16" /> Delete
                                </button>
                            </form>
                        @endunless
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
