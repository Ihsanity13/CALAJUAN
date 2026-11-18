@extends('layouts.app')

@section('title', 'Review ' . $destination->name)
@section('main_class', 'wide')

@php
    $price = $destination->price();
    $hoursByDay = $destination->hours->keyBy('day');
@endphp

@section('content')
    <a class="back-link" href="{{ route('admin.approvals.index') }}">
        <x-icon name="arrow-left" size="18" /> Approvals
    </a>

    <div class="page-head">
        <div>
            <h1>{{ $destination->name }}</h1>
            <div class="dest-meta">
                <span class="tag">{{ $destination->category->name }}</span>
                <x-status-badge :status="$destination->status" />
            </div>
            <p class="muted">
                Sent by {{ $destination->owner?->name ?? 'unknown' }}
                @if ($destination->owner) ({{ $destination->owner->email }}) @endif
                · {{ $destination->updated_at->diffForHumans() }}
            </p>
        </div>
        <a class="btn btn-outline" href="{{ route('destinations.show', $destination) }}" target="_blank" rel="noopener">
            See it as visitors would <span class="sr-only">(opens in a new tab)</span>
        </a>
    </div>

    <div class="review-grid">
        <div>
            {{-- Everything the owner filled in --}}
            <dl class="review-list">
                <div><dt>Description</dt><dd>{!! $destination->description ? nl2br(e($destination->description)) : '—' !!}</dd></div>
                <div><dt>Address</dt><dd>{{ $destination->address ?: '—' }}</dd></div>
                <div>
                    <dt>Location</dt>
                    <dd>
                        {{ $destination->latitude }}, {{ $destination->longitude }}
                        · <a href="https://www.google.com/maps?q={{ $destination->latitude }},{{ $destination->longitude }}" target="_blank" rel="noopener">Check on Google Maps<span class="sr-only"> (opens in a new tab)</span></a>
                    </dd>
                </div>
                <div><dt>Interests</dt><dd>{{ $destination->interests->pluck('name')->join(', ') ?: '—' }}</dd></div>
                <div>
                    <dt>Price</dt>
                    <dd>
                        {{ $price ? ($price['symbol'] === $price['label'] ? $price['label'] : $price['symbol'] . ' · ' . $price['label']) : '—' }}
                        @if ($destination->price_note) · {{ $destination->price_note }} @endif
                    </dd>
                </div>
                <div><dt>Phone</dt><dd>{{ $destination->phone ?: '—' }}</dd></div>
                <div><dt>Email</dt><dd>{{ $destination->email ?: '—' }}</dd></div>
                <div><dt>Website</dt><dd>{{ $destination->website ?: '—' }}</dd></div>
                <div><dt>Facebook</dt><dd>{{ $destination->facebook ?: '—' }}</dd></div>
                <div>
                    <dt>Opening hours</dt>
                    <dd>
                        @if ($hoursByDay->isEmpty())
                            Not listed
                        @else
                            @foreach (\App\Models\DestinationHour::WEEK_ORDER as $day)
                                {{ \App\Models\DestinationHour::DAYS[$day] }}: {{ $hoursByDay->get($day)?->label() ?? 'Closed' }}<br>
                            @endforeach
                        @endif
                    </dd>
                </div>
            </dl>

            <h2 class="review-subhead">Photos ({{ $destination->images->count() }})</h2>
            @if ($destination->images->isEmpty())
                <p class="muted">No photos.</p>
            @else
                <ul class="photo-grid" role="list">
                    @foreach ($destination->images as $photo)
                        <li class="photo-item">
                            <div class="photo-thumb">
                                <a href="{{ $photo->url }}" target="_blank" rel="noopener">
                                    <img src="{{ $photo->url }}" alt="Photo {{ $loop->iteration }} of {{ $destination->name }}"
                                         loading="lazy" width="320" height="240">
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($destination->status === \App\Models\Destination::STATUS_PENDING)
            @include('admin.approvals._decision', [
                'approveUrl' => route('admin.approvals.places.approve', $destination),
                'rejectUrl' => route('admin.approvals.places.reject', $destination),
                'what' => 'this place',
            ])
        @else
            <x-alert type="info">This place isn't waiting for approval right now.</x-alert>
        @endif
    </div>
@endsection
