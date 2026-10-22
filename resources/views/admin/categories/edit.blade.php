@extends('layouts.app')

@section('title', 'Edit category')

@section('content')
    <h1>Edit category</h1>

    {{-- Updates are sent as PUT, so @method('PUT') adds the hidden override field. --}}
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @method('PUT')
        @include('admin.categories._form')
    </form>
@endsection