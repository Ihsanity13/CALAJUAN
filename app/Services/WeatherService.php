<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

// Current weather for Calauan, Laguna, from the free Open-Meteo API (no API key).
//
// Rules from our plan:
// - Never show an error and never slow the page down much:
//   at most 8 seconds of waiting, and failures return null (the card just hides).
// - Cache good results for 30 minutes, so most page loads don't call the API at all.
// - Cache failures for 5 minutes, so a broken API delays at most one page
//   view every 5 minutes instead of every single page view.
// - When the API doesn't answer, show the LAST GOOD reading if it's under
//   6 hours old (the card says "Updated 4:30 AM", so visitors see its age).
//   The connection to Open-Meteo's server can be slow or patchy from here.
class WeatherService
{
    // Calauan, Laguna
    private const LATITUDE = 14.1467;

    private const LONGITUDE = 121.3167;

    private const URL = 'https://api.open-meteo.com/v1/forecast';

    // ":v2" because the cached data's shape changed (tips now have icons, plus "updated").
    // A new key means old cached weather in the old shape is simply ignored.
    private const CACHE_KEY = 'weather:calauan:v2';

    // The last reading that worked, kept for 6 hours as a fallback
    private const LAST_GOOD_KEY = 'weather:calauan:v2:last-good';

    // Returns the weather as an array, or null if it isn't available right now:
    // ['temperature' => 26, 'condition' => 'Mostly clear', 'icon' => 'moon',
    //  'rain_chance' => 89, 'severe' => false, 'updated' => '8:30 PM',
    //  'tips' => [['icon' => 'umbrella', 'text' => 'Bring an umbrella']]]
    public function current(): ?array
    {
        // 1. Cached answer (good or failed) from the last 30 / 5 minutes?
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null) {
            // After a recent failure: the last good reading, or nothing
            return $cached['ok'] ? $cached['weather'] : Cache::get(self::LAST_GOOD_KEY);
        }

        // 2. Ask Open-Meteo
        try {
            $response = Http::connectTimeout(5)   // seconds to connect
                ->timeout(8)                        // seconds in total, then give up
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
            Cache::put(self::LAST_GOOD_KEY, $weather, now()->addHours(6));

            return $weather;
        } catch (Throwable $e) {
            // Timeout, no internet, bad answer...: note it in the log for us
            Log::warning('Weather unavailable: ' . $e->getMessage());

            Cache::put(self::CACHE_KEY, ['ok' => false], now()->addMinutes(5));

            // Show the last good reading if there is one; otherwise the card hides
            return Cache::get(self::LAST_GOOD_KEY);
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

        // Simple advice for visitors. Each tip has an icon too, so the meaning
        // isn't carried by the pill's color alone.
        $tips = [];
        if ($rainChance !== null && $rainChance >= 60) {
            $tips[] = ['icon' => 'umbrella', 'text' => 'Bring an umbrella'];
        }
        if ($temperature >= 33) {
            $tips[] = ['icon' => 'droplet', 'text' => 'Stay hydrated'];
        }

        // When Open-Meteo measured it, in Calauan's time (we asked for Asia/Manila).
        // Shown as "Updated 8:30 PM", because the card can be up to 30 minutes old.
        $measuredAt = data_get($data, 'current.time');

        return [
            'temperature' => (int) round($temperature),
            'condition' => $condition,
            'icon' => $icon,
            'rain_chance' => $rainChance !== null ? (int) $rainChance : null,
            'severe' => $icon === 'storm',
            'updated' => $measuredAt ? Carbon::parse($measuredAt, 'Asia/Manila')->format('g:i A') : null,
            'updated_iso' => $measuredAt ? Carbon::parse($measuredAt, 'Asia/Manila')->toIso8601String() : null,
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
