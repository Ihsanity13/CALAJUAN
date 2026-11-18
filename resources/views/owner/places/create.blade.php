@extends('layouts.app')

@section('title', 'Add a place')
@section('main_class', 'form-wide')

@section('content')
    <a class="back-link" href="{{ route('owner.places.index') }}">
        <x-icon name="arrow-left" size="18" /> My places
    </a>

    <div class="page-head">
        <div>
            <h1>Add a place</h1>
            <p class="muted">Fill in what you can. The Calajuan team checks it before it appears on the site.</p>
        </div>
    </div>

    {{-- The same form the admins use, without the admin-only labels --}}
    <form method="POST" action="{{ route('owner.places.store') }}" enctype="multipart/form-data" novalidate>
        @include('admin.destinations._form', [
            'mode' => 'owner',
            'cancelUrl' => route('owner.places.index'),
            'submitLabel' => 'Send for approval',
        ])
    </form>
@endsection
