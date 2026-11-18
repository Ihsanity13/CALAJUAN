@extends('layouts.app')

@section('title', 'Interests')
@section('main_class', 'wide')

@section('content')
    <a class="back-link" href="{{ route('admin.dashboard') }}">
        <x-icon name="arrow-left" size="18" /> Dashboard
    </a>

    <div class="page-head">
        <div>
            <h1>Interests</h1>
            <p class="muted">
                What places are good for. Visitors pick these to get recommendations.
                {{ $interests->count() }} {{ \Illuminate\Support\Str::plural('interest', $interests->count()) }}.
            </p>
        </div>
        <a class="btn" href="{{ route('admin.interests.create') }}">
            <x-icon name="plus" size="18" />
            New interest
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col" class="cell-num">Places</th>
                    <th scope="col" class="cell-actions"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($interests as $interest)
                    <tr>
                        <td class="cell-strong">{{ $interest->name }}</td>
                        {{-- destinations_count comes from withCount() in the controller --}}
                        <td class="cell-num">{{ $interest->destinations_count }}</td>
                        <td class="cell-actions">
                            <div class="row-actions">
                                <a class="btn btn-ghost btn-sm" href="{{ route('admin.interests.edit', $interest) }}"
                                   aria-label="Edit {{ $interest->name }}">
                                    <x-icon name="pencil" size="16" /> Edit
                                </a>

                                @php
                                    $confirm = 'Delete the interest "' . $interest->name . '"?'
                                        . ($interest->destinations_count > 0
                                            ? ' It will be removed from ' . $interest->destinations_count . ' ' . \Illuminate\Support\Str::plural('place', $interest->destinations_count) . '.'
                                            : '')
                                        . ' This cannot be undone.';
                                @endphp
                                <form method="POST" action="{{ route('admin.interests.destroy', $interest) }}"
                                      onsubmit="return confirm(@js($confirm))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-danger btn-sm"
                                            aria-label="Delete {{ $interest->name }}">
                                        <x-icon name="trash" size="16" /> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="table-empty">
                            No interests yet. <a href="{{ route('admin.interests.create') }}">Add the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
