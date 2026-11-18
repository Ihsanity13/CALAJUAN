@extends('layouts.app')

@section('title', 'Edit destination')
@section('main_class', 'form-wide')

@section('content')
    <a class="back-link" href="{{ route('admin.destinations.index') }}">
        <x-icon name="arrow-left" size="18" /> Destinations
    </a>

    <div class="page-head">
        <h1>Edit destination</h1>
    </div>

    {{-- @method('PUT') makes the browser's POST arrive as an update request. --}}
    <form method="POST" action="{{ route('admin.destinations.update', $destination) }}" novalidate>
        @method('PUT')
        @include('admin.destinations._form')
    </form>
@endsection
