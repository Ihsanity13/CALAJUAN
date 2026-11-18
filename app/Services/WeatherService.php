<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

// Current weather for Calauan, Laguna, from the free Open-Meteo API (no API key).
//
// Rules from our plan:
// - Never show an error and never slow the page down much:
//   at most 5 seconds of waiting, and failures return null (the card just hides).
// - Cache good results for 30 minutes, so most page loads don't call the API at all.
// - Cache failures for 5 minutes, so a broken API delays at most one page
//   view every 5 minutes instead of every single page view.
class WeatherService
{
    // Calauan, Laguna
    private const LATITUDE = 14.1467;
    private const LONGITUDE = 121.3167;

    private const URL = 'https://api.open-meteo.com/v1/forecast';
    private const CACHE_KEY = 'weather:calauan';

    // Returns the weather as an array, or null if it isn't available right now:
    // ['temperature' => 26, 'condition' => 'Mostly clear', 'icon' => 'moon',
    //  'rain_chance' => 89, 'tips' => ['Bring an umbrella']]
    public function current(): ?array
    {
        // 1. Cached answer (good or failed) from the last 30 / 5 minutes?
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null) {
            return $cached['ok'] ? $cached['weather'] : null;
        }

        // 2. Ask Open-Meteo
        try {
            $response = Http::connectTimeout(3)   // seconds to connect
                ->timeout(5)                        // seconds in total, then give up
                ->acceptJson()
                ->get(self::URL, [
                    'latitude' => self::LATITUDE,
                    'longitude' => self::LONGITUDE,
                    'current' => 'temperature_2m,weather_code,is_day',
                    'daily' => 'precipitation_probability_max',
                    'timezone' => 'Asia/Manila',
                    'forecast_days' => 1,
                ])
                ->throw();                          // a 4xx/5xx answer counts as a failure

            $weather = $this->parse($response->json());

            Cache::put(self::CACHE_KEY, ['ok' => true, 'weather' => $weather], now()->addMinutes(30));

            return $weather;
        } catch (Throwable $e) {
            // Timeout, no internet, bad answer...: note it in the log for us, hide the card for visitors
            Log::warning('Weather unavailable: ' . $e->getMessage());

            Cache::put(self::CACHE_KEY, ['ok' => false], now()->addMinutes(5));

            return null;
        }
    }

    // Turn Open-Meteo's raw answer into the few things the card shows.
    private function parse(array $data): array
    {
        $temperature = (float) data_get($data, 'current.temperature_2m');
        $code = (int) data_get($data, 'current.weather_code');
        $isDay = (bool) data_get($data, 'current.is_day', 1);
        $rainChance = data_get($data, 'daily.precipitation_probability_max.0');

        // Missing temperature or weather code = the answer isn't usable
        if (data_get($data, 'current.temperature_2m') === null || data_get($data, 'current.weather_code') === null) {
            throw new \UnexpectedValueException('Weather response is missing fields.');
        }

        [$condition, $icon] = $this->describe($code, $isDay);

        // Simple advice for visitors
        $tips = [];
        if ($rainChance !== null && $rainChance >= 60) {
            $tips[] = 'Bring an umbrella';
        }
        if ($temperature >= 33) {
            $tips[] = 'Stay hydrated';
        }

        return [
            'temperature' => (int) round($temperature),
            'condition' => $condition,
            'icon' => $icon,
            'rain_chance' => $rainChance !== null ? (int) $rainChance : null,
            'tips' => $tips,
        ];
    }

    // WMO weather codes (the numbers Open-Meteo uses) -> words + our icon name.
    // Full list: https://open-meteo.com/en/docs (section "WMO Weather interpretation codes")
    private function describe(int $code, bool $isDay): array
    {
        $clearIcon = $isDay ? 'sun' : 'moon';

        return match (true) {
            $code === 0 => ['Clear sky', $clearIcon],
            $code === 1 => ['Mostly clear', $clearIcon],
            $code === 2 => ['Partly cloudy', 'cloud'],
            $code === 3 => ['Cloudy', 'cloud'],
            in_array($code, [45, 48]) => ['Foggy', 'cloud'],
            $code >= 51 && $code <= 57 => ['Drizzle', 'rain'],
            $code >= 61 && $code <= 67 => ['Rain', 'rain'],
            $code >= 80 && $code <= 82 => ['Rain showers', 'rain'],
            $code >= 95 => ['Thunderstorm', 'storm'],
            default => ['Unsettled weather', 'cloud'],   // snow codes etc. (never in Calauan)
        };
    }
}
