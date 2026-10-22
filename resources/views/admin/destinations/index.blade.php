@extends('layouts.app')

@section('title', 'Destinations')
@section('main_class', 'wide')

@section('content')
    <h1>Destinations</h1>
    <p>
        <a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a> |
        <a href="{{ route('admin.destinations.create') }}">+ New destination</a>
    </p>

    <table>
        <thead>
            <tr><th>Name</th><th>Category</th><th>Featured</th><th>Verified</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse ($destinations as $destination)
                <tr>
                    <td>{{ $destination->name }}</td>
                    {{-- $destination->category uses the belongsTo relationship from the model.
                         It was preloaded with with('category') in the controller. --}}
                    <td>{{ $destination->category->name }}</td>
                    {{-- The ternary "condition ? A : B" prints A when true, B when false.
                         is_featured is a real boolean because of the cast in the model. --}}
                    <td>{{ $destination->is_featured ? 'Yes' : 'No' }}</td>
                    <td>{{ $destination->is_verified ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('admin.destinations.edit', $destination) }}">Edit</a>

                        <form method="POST" action="{{ route('admin.destinations.destroy', $destination) }}"
                              style="display:inline" onsubmit="return confirm('Delete this destination?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No destinations yet.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection