{{-- ALERT: a message box. type = success | error
     Usage: <x-alert type="error">Something went wrong.</x-alert>
     role="status" is read out politely by screen readers; role="alert" interrupts,
     so it's only used for errors. --}}
@props(['type' => 'success'])

<div {{ $attributes->merge([
    'class' => 'alert alert-' . $type,
    'role' => $type === 'error' ? 'alert' : 'status',
]) }}>
    <x-icon :name="$type === 'error' ? 'alert' : 'check'" size="20" />
    <div class="alert-body">{{ $slot }}</div>
</div>
