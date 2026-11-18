{{-- WEATHER CARD (drawn by App\View\Components\WeatherCard).
     Only appears when the weather is available; otherwise nothing is shown. --}}
<section {{ $attributes->merge(['class' => 'card weather-card']) }} aria-label="Weather in Calauan now">
    <span class="weather-icon weather-icon-{{ $weather['icon'] }}">
        <x-icon :name="$weather['icon']" size="40" />
    </span>

    <div class="weather-main">
        <p class="weather-label">Calauan now</p>
        <p class="weather-temp">
            {{ $weather['temperature'] }}<span class="weather-unit">°C</span>
            <span class="weather-condition">{{ $weather['condition'] }}</span>
        </p>
        @if ($weather['rain_chance'] !== null)
            <p class="weather-rain">
                <x-icon name="rain" size="16" />
                {{ $weather['rain_chance'] }}% chance of rain today
            </p>
        @endif
    </div>

    @if (! empty($weather['tips']))
        <ul class="weather-tips" role="list">
            @foreach ($weather['tips'] as $tip)
                <li class="weather-tip">{{ $tip }}</li>
            @endforeach
        </ul>
    @endif
</section>
