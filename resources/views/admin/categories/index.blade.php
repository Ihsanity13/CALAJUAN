@extends('layouts.app')

@section('title', 'Categories')
@section('main_class', 'wide')

@section('content')
    <h1>Categories</h1>
    <p>
        <a href="{{ route('admin.dashboard') }}">&larr; Dashboard</a> |
        <a href="{{ route('admin.categories.create') }}">+ New category</a>
    </p>

    {{-- Shows the error the controller sends when deleting a category that still has destinations. --}}
    @error('category')
        <div class="error">{{ $message }}</div>
    @enderror

    <table>
        <thead>
            <tr><th>Name</th><th>Icon</th><th>Destinations</th><th>Actions</th></tr>
        </thead>
        <tbody>
            {{-- @forelse loops over the list, and runs @empty if the list has no rows. --}}
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->icon }}</td>
                    {{-- destinations_count was added by withCount() in the controller. --}}
                    <td>{{ $category->destinations_count }}</td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $category) }}">Edit</a>

                        {{-- Browsers can only send GET and POST from forms, so @method('DELETE')
                             adds a hidden field telling Laravel to treat it as DELETE.
                             confirm(...) asks "OK?" before the form is sent. --}}
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                              style="display:inline" onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No categories yet.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection