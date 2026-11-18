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
            <p class="muted">{{ $destinations->count() }} {{ \Illuminate\Support\Str::plural('place', $destinations->count()) }}</p>
        </div>
        <a class="btn" href="{{ route('admin.destinations.create') }}">
            <x-icon name="plus" size="18" />
            New destination
        </a>
    </div>

    {{-- .table-wrap scrolls sideways on small screens; the header row stays on top --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Category</th>
                    <th scope="col">Featured</th>
                    <th scope="col">Verified</th>
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
                        </td>
                        {{-- $destination->category uses the belongsTo relationship from the model.
                             It was preloaded with with('category') in the controller. --}}
                        <td><span class="tag">{{ $destination->category->name }}</span></td>
                        {{-- is_featured / is_verified are real booleans because of the casts in the model. --}}
                        <td>
                            @if ($destination->is_featured)
                                <span class="badge badge-yes"><x-icon name="check" size="14" /> Yes</span>
                            @else
                                <span class="badge badge-no">No</span>
                            @endif
                        </td>
                        <td>
                            @if ($destination->is_verified)
                                <span class="badge badge-yes"><x-icon name="verified" size="14" /> Yes</span>
                            @else
                                <span class="badge badge-no">No</span>
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
                            No destinations yet. <a href="{{ route('admin.destinations.create') }}">Add the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
