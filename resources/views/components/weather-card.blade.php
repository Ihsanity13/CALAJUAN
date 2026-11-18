{{-- WEATHER CARD (drawn by App\View\Components\WeatherCard).
     Only appears when the weather is available; otherwise nothing is shown.

     One component, two layouts (CSS container query, see .weather-card in app.css):
     - narrow (destination page sidebar, phones): icon + reading, tips underneath
     - wide (home page): icon | reading | tips in one row

     States: day/night icon, with or without tips, rain chance missing,
     and "severe" (thunderstorm) with an orange accent edge. --}}
<section {{ $attributes->class([
            'card',
            'weather-card',
            'is-severe' => $weather['severe'],
         ]) }}
         aria-labelledby="weather-title">
  {{-- .weather-body holds the grid; the card around it is what the container query measures --}}
  <div class="weather-body">

    {{-- The icon is decoration (aria-hidden inside x-icon); the condition is written out in words --}}
    <span @class(['weather-icon', 'weather-icon-' . $weather['icon']])>
        <x-icon :name="$weather['icon']" size="40" />
    </span>

    <div class="weather-main">
        <h2 class="weather-label" id="weather-title">Weather in Calauan</h2>

        <p class="weather-reading">
            <span class="weather-temp">{{ $weather['temperature'] }}<span class="weather-unit">°C</span></span>
            <span class="weather-condition">{{ $weather['condition'] }}</span>
        </p>

        <p class="weather-meta">
            @if ($weather['rain_chance'] !== null)
                <span class="weather-meta-item">
                    <x-icon name="rain" size="16" />
                    {{ $weather['rain_chance'] }}% chance of rain today
                </span>
            @endif
            @if ($weather['updated'])
                <span class="weather-meta-item">
                    Updated <time datetime="{{ $weather['updated_iso'] }}">{{ $weather['updated'] }}</time>
                </span>
            @endif
        </p>
    </div>

    @if (! empty($weather['tips']))
        <ul class="weather-tips" role="list" aria-label="Tips for today">
            @foreach ($weather['tips'] as $tip)
                <li class="weather-tip">
                    <x-icon :name="$tip['icon']" size="16" />
                    {{ $tip['text'] }}
                </li>
            @endforeach
        </ul>
    @endif
  </div>
</section>
