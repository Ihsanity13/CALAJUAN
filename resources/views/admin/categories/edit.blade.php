@extends('layouts.app')

@section('title', 'Edit category')

@section('content')
    <a class="back-link" href="{{ route('admin.categories.index') }}">
        <x-icon name="arrow-left" size="18" /> Categories
    </a>

    <div class="page-head">
        <h1>Edit category</h1>
    </div>

    {{-- Updates are sent as PUT, so @method('PUT') adds the hidden override field. --}}
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" novalidate>
        @method('PUT')
        @include('admin.categories._form')
    </form>
@endsection
