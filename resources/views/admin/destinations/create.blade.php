@extends('layouts.app')

@section('title', 'New destination')
@section('main_class', 'form-wide')

@section('content')
    <a class="back-link" href="{{ route('admin.destinations.index') }}">
        <x-icon name="arrow-left" size="18" /> Destinations
    </a>

    <div class="page-head">
        <h1>New destination</h1>
    </div>

    {{-- novalidate: Laravel checks the fields, so errors show in our style under each field. --}}
    <form method="POST" action="{{ route('admin.destinations.store') }}" enctype="multipart/form-data" novalidate>
        @include('admin.destinations._form')
    </form>
@endsection
