@extends('layouts.app')

@section('title', 'Verify ' . $verification->business_name)
@section('main_class', 'wide')

@section('content')
    <a class="back-link" href="{{ route('admin.approvals.index') }}">
        <x-icon name="arrow-left" size="18" /> Approvals
    </a>

    <div class="page-head">
        <div>
            <h1>{{ $verification->business_name }}</h1>
            <p class="muted">
                Business owner account of {{ $verification->user->name }} ({{ $verification->user->email }})
                · sent {{ $verification->updated_at->diffForHumans() }}
            </p>
        </div>
    </div>

    <div class="review-grid">
        <div>
            <dl class="review-list">
                <div><dt>Business name</dt><dd>{{ $verification->business_name }}</dd></div>
                <div><dt>Type of business</dt><dd>{{ $verification->category?->name ?? '—' }}</dd></div>
                <div><dt>Address</dt><dd>{{ $verification->business_address }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $verification->business_phone }}</dd></div>
                <div><dt>Their role</dt><dd>{{ $verification->applicantRoleLabel() }}</dd></div>
                <div><dt>Registration</dt><dd>{{ $verification->registrationTypeLabel() }}</dd></div>
                <div><dt>Number</dt><dd><strong>{{ $verification->registration_number }}</strong></dd></div>
            </dl>

            {{-- THE DOCUMENT: served by an admin-only route from the private disk --}}
            <h2 class="review-subhead">Permit / certificate</h2>
            @if (! $verification->document_path)
                <p class="muted">No document uploaded.</p>
            @elseif ($verification->documentIsPdf())
                <a class="btn btn-outline" href="{{ route('admin.approvals.owners.document', $verification) }}" target="_blank" rel="noopener">
                    Open the PDF <span class="sr-only">(opens in a new tab)</span>
                </a>
            @else
                <a href="{{ route('admin.approvals.owners.document', $verification) }}" target="_blank" rel="noopener" class="document-preview">
                    <img src="{{ route('admin.approvals.owners.document', $verification) }}"
                         alt="Uploaded permit or certificate of {{ $verification->business_name }}">
                    <span class="sr-only">(opens full size in a new tab)</span>
                </a>
            @endif

            {{-- A short checklist, so every admin checks the same things --}}
            <h2 class="review-subhead">Before approving, check that</h2>
            <ul class="checklist">
                <li>The business name and number on the document match what they typed.</li>
                <li>The document looks genuine and isn't expired.</li>
                <li>
                    A DTI number shows up in the DTI Business Name Search
                    (<a href="https://bnrs.dti.gov.ph" target="_blank" rel="noopener">bnrs.dti.gov.ph<span class="sr-only"> (opens in a new tab)</span></a>).
                </li>
                <li>If anything looks off, call the business phone above to confirm.</li>
            </ul>
        </div>

        @if ($verification->status === \App\Models\OwnerVerification::STATUS_PENDING)
            @include('admin.approvals._decision', [
                'approveUrl' => route('admin.approvals.owners.approve', $verification),
                'rejectUrl' => route('admin.approvals.owners.reject', $verification),
                'what' => 'this business owner',
            ])
        @else
            <x-alert type="info">
                Already {{ $verification->status }}
                @if ($verification->reviewer) by {{ $verification->reviewer->name }} @endif
                @if ($verification->reviewed_at) on {{ $verification->reviewed_at->timezone(\App\Models\Destination::TIMEZONE)->format('M j, Y') }}@endif.
            </x-alert>
        @endif
    </div>
@endsection
