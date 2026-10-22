@extends('layouts.app')

@section('title', 'New category')

@section('content')
    <h1>New category</h1>

    {{-- POST to the "store" route, which saves the new category. --}}
    <form method="POST" action="{{ route('admin.categories.store') }}">
        @include('admin.categories._form')
    </form>
@endsection