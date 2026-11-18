@extends('layouts.app')

@section('title', 'Approvals')
@section('main_class', 'wide')

@php
    $total = $owners->count() + $places->count() + $changes->count();
    $tabs = [
        'all' => ['label' => 'All', 'count' => $total],
        'owners' => ['label' => 'Business owners', 'count' => $owners->count()],
        'places' => ['label' => 'New places', 'count' => $places->count()],
        'changes' => ['label' => 'Changes', 'count' => $changes->count()],
    ];
    $show = fn (string $section) => $tab === 'all' || $tab === $section;
@endphp

@section('content')
    <a class="back-link" href="{{ route('admin.dashboard') }}">
        <x-icon name="arrow-left" size="18" /> Dashboard
    </a>

    <div class="page-head">
        <div>
            <h1>Approvals</h1>
            <p class="muted">
                @if ($total === 0)
                    Nothing is waiting. Nice work.
                @else
                    {{ $total }} {{ \Illuminate\Support\Str::plural('submission', $total) }} waiting, oldest first.
                    Nothing here is visible to visitors until you approve it.
                @endif
            </p>
        </div>
    </div>

    {{-- TABS: one chip per kind, with how many are waiting --}}
    <nav class="chip-row filter-chips" aria-label="Show">
        @foreach ($tabs as $key => $t)
            <a class="chip" href="{{ route('admin.approvals.index', $key === 'all' ? [] : ['tab' => $key]) }}"
               @if ($tab === $key) aria-current="page" @endif>
                {{ $t['label'] }}
                <span @class(['chip-count', 'chip-count-hot' => $t['count'] > 0])>{{ $t['count'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="approvals-layout">
        <div class="approvals-main">
            @if ($total === 0 && $tab === 'all')
                {{-- ALL CAUGHT UP --}}
                <div class="card all-clear">
                    <span class="all-clear-icon" aria-hidden="true"><x-icon name="check" size="32" /></span>
                    <h2>All caught up</h2>
                    <p class="muted">New business owners, places and changes will show up here.</p>
                </div>
            @endif

            {{-- 1. BUSINESS OWNERS: they can't add places until they're verified, so they come first --}}
            @if ($show('owners') && ($tab === 'owners' || $owners->isNotEmpty()))
                <section class="approval-section" aria-labelledby="owners-heading">
                    <h2 id="owners-heading" class="approval-heading">
                        Business owners to verify <span class="heading-count">({{ $owners->count() }})</span>
                    </h2>
                    @if ($owners->isEmpty())
                        <p class="muted">No business owners waiting.</p>
                    @else
                        <ul class="approval-list" role="list">
                            @foreach ($owners as $verification)
                                @include('admin.approvals._item', [
                                    'url' => route('admin.approvals.owners.show', $verification),
                                    'kind' => 'owner',
                                    'kindLabel' => 'Business owner',
                                    'title' => $verification->business_name,
                                    'summary' => $verification->registrationTypeLabel() . ' · No. ' . $verification->registration_number,
                                    'sentBy' => $verification->user->name,
                                    'sentEmail' => $verification->user->email,
                                    'sentAt' => $verification->updated_at,
                                    'thumb' => null,
                                    'icon' => 'user',
                                ])
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif

            {{-- 2. NEW PLACES --}}
            @if ($show('places') && ($tab === 'places' || $places->isNotEmpty()))
                <section class="approval-section" aria-labelledby="places-heading">
                    <h2 id="places-heading" class="approval-heading">
                        New places <span class="heading-count">({{ $places->count() }})</span>
                    </h2>
                    @if ($places->isEmpty())
                        <p class="muted">No new places waiting.</p>
                    @else
                        <ul class="approval-list" role="list">
                            @foreach ($places as $place)
                                @include('admin.approvals._item', [
                                    'url' => route('admin.approvals.places.show', $place),
                                    'kind' => 'place',
                                    'kindLabel' => 'New place',
                                    'title' => $place->name,
                                    'summary' => $place->category->name . ($place->address ? ' · ' . $place->address : ''),
                                    'sentBy' => $place->owner?->name,
                                    'sentEmail' => $place->owner?->email,
                                    'sentAt' => $place->updated_at,
                                    'thumb' => $place->primaryImage?->url,
                                    'icon' => 'map-pin',
                                ])
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif

            {{-- 3. CHANGES TO LIVE PLACES --}}
            @if ($show('changes') && ($tab === 'changes' || $changes->isNotEmpty()))
                <section class="approval-section" aria-labelledby="changes-heading">
                    <h2 id="changes-heading" class="approval-heading">
                        Changes to live places <span class="heading-count">({{ $changes->count() }})</span>
                    </h2>
                    @if ($changes->isEmpty())
                        <p class="muted">No changes waiting.</p>
                    @else
                        <ul class="approval-list" role="list">
                            @foreach ($changes as $change)
                                @php
                                    // "Changed: Name, Phone + 2 new photos"
                                    $parts = $change->changed_labels;
                                    $photoCount = $change->images->count();
                                    $summary = ($parts ? 'Changed: ' . implode(', ', $parts) : 'No detail changes')
                                        . ($photoCount ? ' + ' . $photoCount . ' new ' . \Illuminate\Support\Str::plural('photo', $photoCount) : '');
                                @endphp
                                @include('admin.approvals._item', [
                                    'url' => route('admin.approvals.changes.show', $change),
                                    'kind' => 'change',
                                    'kindLabel' => 'Changes',
                                    'title' => $change->destination->name,
                                    'summary' => $summary,
                                    'sentBy' => $change->user?->name,
                                    'sentEmail' => $change->user?->email,
                                    'sentAt' => $change->created_at,
                                    // the first new photo, else the place's cover photo
                                    'thumb' => $change->images->first()?->url ?? $change->destination->primaryImage?->url,
                                    'icon' => 'pencil',
                                ])
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endif
        </div>

        {{-- RECENT DECISIONS: who approved or rejected what --}}
        <aside class="card recent-card" aria-labelledby="recent-heading">
            <h2 id="recent-heading">Recent decisions</h2>
            @if (empty($recent))
                <p class="muted">No decisions yet.</p>
            @else
                <ul class="recent-list" role="list">
                    @foreach ($recent as $row)
                        <li>
                            <span @class(['recent-dot', 'is-approved' => $row['status'] === 'approved', 'is-rejected' => $row['status'] !== 'approved'])
                                  aria-hidden="true">
                                <x-icon :name="$row['status'] === 'approved' ? 'check' : 'alert'" size="14" />
                            </span>
                            <div>
                                <a href="{{ $row['url'] }}">{{ $row['title'] }}</a>
                                <p class="recent-meta">
                                    {{ $row['kind'] }} · {{ $row['status'] === 'approved' ? 'Approved' : 'Rejected' }}
                                    @if ($row['by']) by {{ $row['by'] }} @endif
                                    · {{ $row['at']->diffForHumans() }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </div>
@endsection
