{{-- APPROVAL STATUS BADGE: Live / Waiting for approval / Not approved.
     Usage: <x-status-badge :status="$destination->status" />
     Words and an icon, so the meaning doesn't depend on color alone. --}}
@props(['status'])

@switch($status)
    @case(\App\Models\Destination::STATUS_APPROVED)
        <span {{ $attributes->merge(['class' => 'badge badge-live']) }}><x-icon name="check" size="14" /> Live</span>
        @break
    @case(\App\Models\Destination::STATUS_PENDING)
        <span {{ $attributes->merge(['class' => 'badge badge-pending']) }}><x-icon name="clock" size="14" /> Waiting for approval</span>
        @break
    @case(\App\Models\Destination::STATUS_REJECTED)
        <span {{ $attributes->merge(['class' => 'badge badge-rejected']) }}><x-icon name="alert" size="14" /> Not approved</span>
        @break
@endswitch
