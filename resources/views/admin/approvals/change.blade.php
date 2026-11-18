@extends('layouts.app')

@section('title', 'Review changes to ' . $change->destination->name)
@section('main_class', 'wide')

@section('content')
    <a class="back-link" href="{{ route('admin.approvals.index') }}">
        <x-icon name="arrow-left" size="18" /> Approvals
    </a>

    <div class="page-head">
        <div>
            <h1>Changes to {{ $change->destination->name }}</h1>
            <p class="muted">
                Sent by {{ $change->user?->name ?? 'unknown' }}
                @if ($change->user) ({{ $change->user->email }}) @endif
                · {{ $change->created_at->diffForHumans() }}
            </p>
        </div>
        <a class="btn btn-outline" href="{{ route('destinations.show', $change->destination) }}" target="_blank" rel="noopener">
            See the live page <span class="sr-only">(opens in a new tab)</span>
        </a>
    </div>

    <div class="review-grid">
        <div>
            {{-- WHAT CHANGED: only the things that are different (DestinationChange::differences()) --}}
            @if (empty($differences) && $change->images->isEmpty())
                <x-alert type="info">Nothing is different from the live version.</x-alert>
            @elseif (! empty($differences))
                <div class="table-wrap">
                    <table class="diff-table">
                        <thead>
                            <tr>
                                <th scope="col">What</th>
                                <th scope="col">Now</th>
                                <th scope="col">Proposed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($differences as $row)
                                <tr>
                                    <th scope="row">{{ $row['label'] }}</th>
                                    {{-- nl2br(e(...)): keep line breaks (hours are one day per line), safely escaped --}}
                                    <td class="diff-old">{!! nl2br(e($row['current'])) !!}</td>
                                    <td class="diff-new">{!! nl2br(e($row['proposed'])) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($change->images->isNotEmpty())
                <h2 class="review-subhead">New photos ({{ $change->images->count() }})</h2>
                <ul class="photo-grid" role="list">
                    @foreach ($change->images as $photo)
                        <li class="photo-item">
                            <div class="photo-thumb">
                                <a href="{{ $photo->url }}" target="_blank" rel="noopener">
                                    <img src="{{ $photo->url }}" alt="New photo {{ $loop->iteration }}"
                                         loading="lazy" width="320" height="240">
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($change->status === \App\Models\DestinationChange::STATUS_PENDING)
            @include('admin.approvals._decision', [
                'approveUrl' => route('admin.approvals.changes.approve', $change),
                'rejectUrl' => route('admin.approvals.changes.reject', $change),
                'what' => 'these changes',
            ])
        @else
            <x-alert type="info">These changes were already {{ $change->status }}.</x-alert>
        @endif
    </div>
@endsection
