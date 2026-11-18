<?php

namespace App\View\Components;

use App\Services\WeatherService;
use Illuminate\View\Component;
use Illuminate\View\View;

// <x-weather-card /> : a small "weather in Calauan right now" card.
// A class-based component, so any page can drop it in without its
// controller having to fetch the weather first.
class WeatherCard extends Component
{
    public ?array $weather;

    // Laravel creates the WeatherService for us (dependency injection)
    public function __construct(WeatherService $service)
    {
        $this->weather = $service->current();
    }

    // No weather (API down, timeout...) = the card isn't drawn at all
    public function shouldRender(): bool
    {
        return $this->weather !== null;
    }

    public function render(): View
    {
        return view('components.weather-card');
    }
}
