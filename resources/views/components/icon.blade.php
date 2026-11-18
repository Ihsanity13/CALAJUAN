{{-- ICON: a small hand-drawn SVG icon set, so we don't need an icon library or emoji.
     Usage: <x-icon name="heart" />  or  <x-icon name="star" size="16" fill="currentColor" />
     Icons are decorative (aria-hidden), so always put a visible text label next to them,
     or an aria-label on the button/link that contains them. --}}
@props(['name', 'size' => 24])

<svg {{ $attributes->merge([
    'class' => 'icon',
    'width' => $size,
    'height' => $size,
    'viewBox' => '0 0 24 24',
    'fill' => 'none',
    'stroke' => 'currentColor',
    'stroke-width' => '1.8',
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'aria-hidden' => 'true',
    'focusable' => 'false',
]) }}>
    @switch($name)
        @case('compass')
            <circle cx="12" cy="12" r="9" /><path d="m15.5 8.5-2 5-5 2 2-5z" />
            @break
        @case('heart')
            <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" />
            @break
        @case('user')
            <circle cx="12" cy="8" r="4" /><path d="M4 20c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5" />
            @break
        @case('grid')
            <rect x="4" y="4" width="7" height="7" rx="1.5" /><rect x="13" y="4" width="7" height="7" rx="1.5" />
            <rect x="4" y="13" width="7" height="7" rx="1.5" /><rect x="13" y="13" width="7" height="7" rx="1.5" />
            @break
        @case('map-pin')
            <path d="M12 21s-6-5.4-6-11a6 6 0 0 1 12 0c0 5.6-6 11-6 11z" /><circle cx="12" cy="10" r="2.2" />
            @break
        @case('tag')
            <path d="M3.5 12.5V4.5a1 1 0 0 1 1-1h8l8 8-9 9z" /><circle cx="8" cy="8" r="1.4" />
            @break
        @case('check')
            <path d="m5 12.5 4.5 4.5L19 7.5" />
            @break
        @case('verified')
            <circle cx="12" cy="12" r="9" /><path d="m8.5 12.2 2.4 2.4 4.6-4.8" />
            @break
        @case('star')
            <path d="m12 3.5 2.6 5.3 5.8.8-4.2 4.1 1 5.8L12 16.8l-5.2 2.7 1-5.8-4.2-4.1 5.8-.8z" />
            @break
        @case('pencil')
            <path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16z" /><path d="m13.5 6.5 4 4" />
            @break
        @case('trash')
            <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" />
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break
        @case('arrow-left')
            <path d="M19 12H5M11 6l-6 6 6 6" />
            @break
        @case('arrow-right')
            <path d="M5 12h14M13 6l6 6-6 6" />
            @break
        @case('login')
            <path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4" /><path d="m10 8 4 4-4 4M14 12H4" />
            @break
        @case('logout')
            <path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4" /><path d="m15 8 4 4-4 4M19 12H9" />
            @break
        {{-- place details --}}
        @case('clock')
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
            @break
        @case('phone')
            <path d="M5 4h3.5l1.5 4-2 1.5a11 11 0 0 0 6.5 6.5L16 14l4 1.5V19a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1z" />
            @break
        @case('mail')
            <rect x="3.5" y="5.5" width="17" height="13" rx="2" /><path d="m4 7 8 6 8-6" />
            @break
        @case('globe')
            <circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
            @break
        @case('facebook')
            <rect x="3.5" y="3.5" width="17" height="17" rx="4" /><path d="M15 8h-1.5A2.5 2.5 0 0 0 11 10.5V20M9 13h5" />
            @break
        {{-- weather icons --}}
        @case('sun')
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2.5v2M12 19.5v2M4.6 4.6l1.4 1.4M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4 6 18M18 6l1.4-1.4" />
            @break
        @case('moon')
            <path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z" />
            @break
        @case('cloud')
            <path d="M7 18.5h10a4 4 0 0 0 .6-7.95A5.5 5.5 0 0 0 7.1 9.6 4.5 4.5 0 0 0 7 18.5z" />
            @break
        @case('rain')
            <path d="M7 14.5h10a4 4 0 0 0 .6-7.95A5.5 5.5 0 0 0 7.1 5.6 4.5 4.5 0 0 0 7 14.5z" />
            <path d="M8.5 17.5 7.5 20M12.5 17.5l-1 2.5M16.5 17.5l-1 2.5" />
            @break
        @case('storm')
            <path d="M7 14.5h10a4 4 0 0 0 .6-7.95A5.5 5.5 0 0 0 7.1 5.6 4.5 4.5 0 0 0 7 14.5z" />
            <path d="m12.5 14.5-2 3.5h3l-2 3.5" />
            @break
        @case('umbrella')
            <path d="M3 12a9 9 0 0 1 18 0z" /><path d="M12 12v6.5a2 2 0 0 1-4 0" />
            @break
        @case('droplet')
            <path d="M12 3s6 6.4 6 10.5a6 6 0 0 1-12 0C6 9.4 12 3 12 3z" />
            @break
        @case('alert')
            <circle cx="12" cy="12" r="9" /><path d="M12 7.5v5.5M12 16.5h.01" />
            @break
    @endswitch
</svg>
