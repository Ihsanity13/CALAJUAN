@extends('layouts.app')

@section('title', 'Edit destination')

@section('content')
    <h1>Edit destination</h1>

    {{-- @method('PUT') makes the browser's POST arrive as an update request. --}}
    <form method="POST" action="{{ route('admin.destinations.update', $destination) }}">
        @method('PUT')
        @include('admin.destinations._form')
    </form>
@endsection