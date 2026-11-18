@extends('layouts.app')

@section('title', 'Categories')
@section('main_class', 'wide')

@section('content')
    <a class="back-link" href="{{ route('admin.dashboard') }}">
        <x-icon name="arrow-left" size="18" /> Dashboard
    </a>

    <div class="page-head">
        <div>
            <h1>Categories</h1>
            <p class="muted">{{ $categories->count() }} {{ \Illuminate\Support\Str::plural('category', $categories->count()) }}</p>
        </div>
        <a class="btn" href="{{ route('admin.categories.create') }}">
            <x-icon name="plus" size="18" />
            New category
        </a>
    </div>

    {{-- Shows the error the controller sends when deleting a category that still has destinations. --}}
    @error('category')
        <div class="flash"><x-alert type="error">{{ $message }}</x-alert></div>
    @enderror

    {{-- .table-wrap scrolls sideways on small screens; the header row stays on top --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col" class="cell-num">Destinations</th>
                    <th scope="col" class="cell-actions"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                {{-- @forelse loops over the list, and runs @empty if the list has no rows. --}}
                @forelse ($categories as $category)
                    <tr>
                        <td class="cell-strong">{{ $category->name }}</td>
                        {{-- destinations_count was added by withCount() in the controller. --}}
                        <td class="cell-num">{{ $category->destinations_count }}</td>
                        <td class="cell-actions">
                            <div class="row-actions">
                                {{-- aria-label adds the name, so screen readers hear "Edit Beaches", not just "Edit" --}}
                                <a class="btn btn-ghost btn-sm" href="{{ route('admin.categories.edit', $category) }}"
                                   aria-label="Edit {{ $category->name }}">
                                    <x-icon name="pencil" size="16" /> Edit
                                </a>

                                {{-- Browsers can only send GET and POST from forms, so @method('DELETE')
                                     adds a hidden field telling Laravel to treat it as DELETE.
                                     confirm(...) asks "OK?" before the form is sent;
                                     @js() safely puts the name inside the JavaScript text. --}}
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                      onsubmit="return confirm(@js('Delete the category "' . $category->name . '"? This cannot be undone.'))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-danger btn-sm"
                                            aria-label="Delete {{ $category->name }}">
                                        <x-icon name="trash" size="16" /> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="table-empty">
                            No categories yet. <a href="{{ route('admin.categories.create') }}">Add the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
