@extends('layouts.app')

@section('title', 'New interest')

@section('content')
    <a class="back-link" href="{{ route('admin.interests.index') }}">
        <x-icon name="arrow-left" size="18" /> Interests
    </a>

    <div class="page-head">
        <h1>New interest</h1>
    </div>

    <form method="POST" action="{{ route('admin.interests.store') }}" novalidate>
        @include('admin.interests._form')
    </form>
@endsection
