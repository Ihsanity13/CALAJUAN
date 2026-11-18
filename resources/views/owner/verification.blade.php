@extends('layouts.app')

@section('title', 'Business verification')
@section('main_class', 'form-wide')

{{-- $verification: the business details this owner sent, or null if none yet --}}
@section('content')
    <div class="page-head">
        <div>
            <h1>Verify your business</h1>
            <p class="muted">Before you can add places, the Calajuan team checks that your business is real.</p>
        </div>
    </div>

    @php
        $status = $verification?->status;
        // The form shows when nothing was sent yet (draft / none) or it was rejected
        $canSend = ! $verification || $verification->canBeSent();
    @endphp

    @if ($status === \App\Models\OwnerVerification::STATUS_PENDING)
        <x-alert type="warning" class="form-notice">
            <strong>We're checking your business.</strong>
            This usually takes 1 to 2 working days. We may call your business phone to confirm.
            You can add places as soon as it's approved.
        </x-alert>
    @elseif ($status === \App\Models\OwnerVerification::STATUS_REJECTED)
        <x-alert type="error" class="form-notice">
            <strong>We couldn't verify your business:</strong> {{ $verification->rejection_reason }}
            Correct the details below and send them again.
        </x-alert>
    @else
        {{-- Just signed up (draft), or no details at all --}}
        <x-alert type="info" class="form-notice">
            <strong>Step 2 of 2.</strong>
            Add your business address and permit details, and upload a photo of your permit or certificate.
            We usually check them within 1 to 2 working days.
        </x-alert>
    @endif

    {{-- What was sent (while it's being checked) --}}
    @if ($status === \App\Models\OwnerVerification::STATUS_PENDING)
        <section class="card info-card" aria-labelledby="sent-heading">
            <h2 id="sent-heading">What you sent</h2>
            <dl class="review-list">
                <div><dt>Business</dt><dd>{{ $verification->business_name }}</dd></div>
                <div><dt>Type</dt><dd>{{ $verification->category?->name ?? '—' }}</dd></div>
                <div><dt>Address</dt><dd>{{ $verification->business_address }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $verification->business_phone }}</dd></div>
                <div><dt>Your role</dt><dd>{{ $verification->applicantRoleLabel() }}</dd></div>
                <div><dt>Registration</dt><dd>{{ $verification->registrationTypeLabel() }}</dd></div>
                <div><dt>Number</dt><dd>{{ $verification->registration_number }}</dd></div>
                <div><dt>Sent</dt><dd>{{ $verification->updated_at->timezone(\App\Models\Destination::TIMEZONE)->format('M j, Y g:i A') }}</dd></div>
            </dl>
        </section>
    @endif

    {{-- The form (no details yet, or they were rejected) --}}
    @if ($canSend)
        <form method="POST" action="{{ route('owner.verification.update') }}" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            @include('owner._business-fields', ['verification' => $verification])

            <div class="form-actions">
                <button type="submit" class="btn">
                    <x-icon name="check" size="18" /> Send for verification
                </button>
            </div>
        </form>
    @endif
@endsection
