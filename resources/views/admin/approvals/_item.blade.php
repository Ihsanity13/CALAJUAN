{{-- ONE ITEM in the approval queue, as a card. Used for owners, new places and changes.
     Needs:
       $url        the review page
       $kind       'owner' | 'place' | 'change' (sets the badge color)
       $kindLabel  e.g. "New place"
       $title      the big line
       $summary    one line under the title (already in words)
       $sentBy     who sent it (name), $sentEmail (optional)
       $sentAt     Carbon date it was sent
       $thumb      image URL, or null for an icon
       $icon       icon name when there's no image
     Anything waiting 2 days or more gets an orange "Waiting X days" badge. --}}
@php
    $days = (int) $sentAt->diffInDays(now());
@endphp

<li class="card approval-card">
    <div class="approval-thumb">
        @if ($thumb)
            <img src="{{ $thumb }}" alt="" loading="lazy" width="96" height="96">
        @else
            <span class="approval-thumb-icon approval-thumb-{{ $kind }}" aria-hidden="true">
                <x-icon :name="$icon" size="28" />
            </span>
        @endif
    </div>

    <div class="approval-body">
        <div class="approval-badges">
            <span class="badge kind-badge kind-{{ $kind }}">{{ $kindLabel }}</span>
            @if ($days >= 2)
                <span class="badge badge-waiting">
                    <x-icon name="clock" size="14" /> Waiting {{ $days }} days
                </span>
            @endif
        </div>

        <h3 class="approval-title">
            {{-- The title is the link; CSS stretches its click area over the whole card --}}
            <a class="approval-link" href="{{ $url }}">{{ $title }}</a>
        </h3>

        @if ($summary)
            <p class="approval-summary">{{ $summary }}</p>
        @endif

        <p class="approval-meta">
            <x-icon name="user" size="14" />
            {{ $sentBy ?? 'Unknown' }}
            @isset($sentEmail) <span class="muted">· {{ $sentEmail }}</span> @endisset
            <span aria-hidden="true">·</span>
            <time datetime="{{ $sentAt->toIso8601String() }}" title="{{ $sentAt->timezone(\App\Models\Destination::TIMEZONE)->format('M j, Y g:i A') }}">
                {{ $sentAt->diffForHumans() }}
            </time>
        </p>
    </div>

    <span class="btn btn-sm approval-cta" aria-hidden="true">
        Review <x-icon name="arrow-right" size="16" />
    </span>
</li>
