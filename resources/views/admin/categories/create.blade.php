@extends('layouts.app')

@section('title', 'New category')

@section('content')
    <a class="back-link" href="{{ route('admin.categories.index') }}">
        <x-icon name="arrow-left" size="18" /> Categories
    </a>

    <div class="page-head">
        <h1>New category</h1>
    </div>

    {{-- POST to the "store" route, which saves the new category.
         novalidate: Laravel checks the fields, so errors show in our style under each field. --}}
    <form method="POST" action="{{ route('admin.categories.store') }}" novalidate>
        @include('admin.categories._form')
    </form>
@endsection
