@extends('layouts.app')

@section('title', 'Edit ' . $live->name)
@section('main_class', 'form-wide')

{{-- $live = the place as it is now. $destination = what the form shows:
     the waiting changes if there are any, otherwise the same as $live. --}}
@section('content')
    <a class="back-link" href="{{ route('owner.places.index') }}">
        <x-icon name="arrow-left" size="18" /> My places
    </a>

    <div class="page-head">
        <div>
            <h1>Edit {{ $live->name }}</h1>
            <div class="dest-meta">
                <x-status-badge :status="$live->status" />
            </div>
        </div>
    </div>

    {{-- What happens when you save, depending on the place's status --}}
    @if ($pendingChange)
        <x-alert type="warning" class="form-notice">
            <p>
                <strong>Your changes from {{ $pendingChange->created_at->timezone(\App\Models\Destination::TIMEZONE)->format('M j, g:i A') }} are waiting for approval.</strong>
                The form below shows them. Visitors still see the current version.
                Saving again replaces your waiting changes.
            </p>
            {{-- Submits the small form printed after the main form (forms can't be nested) --}}
            <button type="submit" class="btn btn-ghost btn-danger btn-sm" form="withdraw-changes">
                Withdraw my changes
            </button>
        </x-alert>
    @elseif ($live->isApproved())
        <x-alert type="info" class="form-notice">
            Your place is live. Changes you save here, including new photos, are checked by the Calajuan team first.
            Visitors keep seeing the current version until then.
        </x-alert>
    @elseif ($live->status === \App\Models\Destination::STATUS_REJECTED)
        <x-alert type="error" class="form-notice">
            <strong>Not approved:</strong> {{ $live->rejection_reason }}
            Fix the details below and save to send it for approval again.
        </x-alert>
    @else
        <x-alert type="warning" class="form-notice">
            Your place is waiting for approval. You can still change it while it waits.
        </x-alert>
    @endif

    <form method="POST" action="{{ route('owner.places.update', $live) }}" enctype="multipart/form-data" novalidate>
        @method('PUT')
        @include('admin.destinations._form', [
            'mode' => 'owner',
            'cancelUrl' => route('owner.places.index'),
            'submitLabel' => $live->isApproved() ? 'Send changes for approval' : 'Save and send for approval',
            'photoNote' => $live->isApproved() ? 'New photos appear after the changes are approved.' : null,
            'pendingPhotos' => $pendingChange?->images ?? collect(),
        ])
    </form>

    @if ($pendingChange)
        <form id="withdraw-changes" method="POST" action="{{ route('owner.places.withdraw', $live) }}"
              onsubmit="return confirm(@js('Withdraw your waiting changes? Any new photos in them are deleted too.'))" hidden>
            @csrf
            @method('DELETE')
        </form>
    @endif

    {{-- The photo buttons in the form above submit THESE forms (form="photo-..."),
         because a form can't sit inside another form --}}
    @foreach ($live->images as $photo)
        <form id="photo-primary-{{ $photo->id }}" method="POST"
              action="{{ route('owner.places.images.primary', [$live, $photo]) }}" hidden>
            @csrf
            @method('PATCH')
        </form>

        @php $confirm = 'Delete photo ' . $loop->iteration . ' of "' . $live->name . '"? This cannot be undone.'; @endphp
        <form id="photo-delete-{{ $photo->id }}" method="POST"
              action="{{ route('owner.places.images.destroy', [$live, $photo]) }}"
              onsubmit="return confirm(@js($confirm))" hidden>
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endsection
