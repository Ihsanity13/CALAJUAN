@extends('layouts.app')

@section('title', 'Edit destination')
@section('main_class', 'form-wide')

@section('content')
    <a class="back-link" href="{{ route('admin.destinations.index') }}">
        <x-icon name="arrow-left" size="18" /> Destinations
    </a>

    <div class="page-head">
        <h1>Edit destination</h1>
    </div>

    {{-- An owner's changes are waiting: editing here doesn't touch them --}}
    @if ($destination->pendingChange)
        <x-alert type="warning" class="form-notice">
            {{ $destination->owner?->name ?? 'The owner' }} sent changes to this place that are waiting for approval.
            <a href="{{ route('admin.approvals.changes.show', $destination->pendingChange) }}">Review them</a>.
            Edits you save here go live right away.
        </x-alert>
    @endif

    {{-- @method('PUT') makes the browser's POST arrive as an update request. --}}
    <form method="POST" action="{{ route('admin.destinations.update', $destination) }}" enctype="multipart/form-data" novalidate>
        @method('PUT')
        @include('admin.destinations._form')
    </form>

    {{-- The photo buttons in the form above submit THESE forms (form="photo-..."),
         because a form can't sit inside another form. They have no visible content. --}}
    @foreach ($destination->images as $photo)
        <form id="photo-primary-{{ $photo->id }}" method="POST"
              action="{{ route('admin.destinations.images.primary', [$destination, $photo]) }}" hidden>
            @csrf
            @method('PATCH')
        </form>

        {{-- confirm(...) asks before deleting; @js() safely puts the text into JavaScript --}}
        <form id="photo-delete-{{ $photo->id }}" method="POST"
              action="{{ route('admin.destinations.images.destroy', [$destination, $photo]) }}"
              onsubmit="return confirm(@js('Delete photo ' . $loop->iteration . ' of "' . $destination->name . '"? This cannot be undone.'))" hidden>
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endsection
