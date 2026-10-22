@extends('layouts.app')

@section('title', 'New destination')

@section('content')
    <h1>New destination</h1>

    <form method="POST" action="{{ route('admin.destinations.store') }}">
        @include('admin.destinations._form')
    </form>
@endsection