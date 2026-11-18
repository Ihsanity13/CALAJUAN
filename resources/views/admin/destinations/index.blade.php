@extends('layouts.app')

@section('title', 'Destinations')
@section('main_class', 'wide')

@section('content')
    <a class="back-link" href="{{ route('admin.dashboard') }}">
        <x-icon name="arrow-left" size="18" /> Dashboard
    </a>

    <div class="page-head">
        <div>
            <h1>Destinations</h1>
            <p class="muted">
                {{ $destinations->count() }} {{ \Illuminate\Support\Str::plural('place', $destinations->count()) }}
                @if ($filter) · showing {{ \App\Models\Destination::LIST_FILTERS[$filter] }} @endif
            </p>
        </div>
        <a class="btn" href="{{ route('admin.destinations.create') }}">
            <x-icon name="plus" size="18" />
            New destination
        </a>
    </div>

    {{-- FILTERS: one chip per filter, the current one marked with aria-current --}}
    <nav class="chip-row filter-chips" aria-label="Filter destinations">
        <a class="chip" href="{{ route('admin.destinations.index') }}"
           @if (! $filter) aria-current="page" @endif>All</a>
        @foreach (\App\Models\Destination::LIST_FILTERS as $key => $label)
            <a class="chip" href="{{ route('admin.destinations.index', ['show' => $key]) }}"
               @if ($filter === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    {{-- .table-wrap scrolls sideways on small screens; the header row stays on top --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Category</th>
                    <th scope="col">Status</th>
                    <th scope="col">Labels</th>
                    <th scope="col" class="cell-actions"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($destinations as $destination)
                    <tr>
                        <td>
                            <span class="cell-strong">{{ $destination->name }}</span>
                            @if ($destination->address)
                                <span class="cell-sub">{{ $destination->address }}</span>
                            @endif
                            @if ($destination->owner)
                                <span class="cell-sub">Owner: {{ $destination->owner->name }}</span>
                            @endif
                        </td>
                        {{-- $destination->category uses the belongsTo relationship from the model.
                             It was preloaded with with('category') in the controller. --}}
                        <td><span class="tag">{{ $destination->category->name }}</span></td>
                        {{-- Live / waiting / not approved, plus a note when an owner's changes are waiting --}}
                        <td>
                            <div class="label-list">
                                <x-status-badge :status="$destination->status" />
                                @if ($destination->pending_change_exists)
                                    <span class="badge badge-pending"><x-icon name="clock" size="14" /> Changes waiting</span>
                                @endif
                            </div>
                        </td>
                        {{-- ⭐ Featured / 🔥 Hot / ✨ New, or a dash when it has none --}}
                        <td>
                            @if ($destination->is_featured || $destination->is_hot || $destination->is_new)
                                <div class="label-list">
                                    <x-place-labels :destination="$destination" featured />
                                </div>
                            @else
                                <span class="badge badge-no">None</span>
                            @endif
                        </td>
                        <td class="cell-actions">
                            <div class="row-actions">
                                <a class="btn btn-ghost btn-sm" href="{{ route('admin.destinations.edit', $destination) }}"
                                   aria-label="Edit {{ $destination->name }}">
                                    <x-icon name="pencil" size="16" /> Edit
                                </a>

                                <form method="POST" action="{{ route('admin.destinations.destroy', $destination) }}"
                                      onsubmit="return confirm(@js('Delete "' . $destination->name . '"? This cannot be undone.'))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-danger btn-sm"
                                            aria-label="Delete {{ $destination->name }}">
                                        <x-icon name="trash" size="16" /> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="table-empty">
                            @if ($filter)
                                No places here. <a href="{{ route('admin.destinations.index') }}">Show all places</a>.
                            @else
                                No destinations yet. <a href="{{ route('admin.destinations.create') }}">Add the first one</a>.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
