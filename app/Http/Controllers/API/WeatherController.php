<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Validator;

/**
 * WeatherController class handles interactions with the weather API service.
 * It provides endpoints for retrieving weather data and managing related functionalities.
 */
class WeatherController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Retrieves weather data for a specific city.
     *
     * @param string $city Name of the city for which weather data is requested.
     * @return \Illuminate\Http\JsonResponse Weather data in JSON format.
     */
    public function getWeatherForCity(string $city)
    {
        $weatherData = $this->weatherService->getWeatherForCity($city);
        return response()->json($weatherData);
    }

    /**
     * Retrieves weather data for a bulk list of cities provided in a request body.
     *
     * @param Request $request Incoming HTTP request containing city names.
     * @return \Illuminate\Http\JsonResponse Weather data for each requested city in JSON format,
     *                                     or validation error response if request data is invalid.
     */
    public function getBulkWeather(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cities' => 'required|array|max:10|min:1',
            'cities.*' => 'required|string|alpha_dash|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cities = $request->input('cities');
        $weatherData = $this->weatherService->getBulkWeather($cities);
        return response()->json($weatherData);
    }

    /**
     * Retrieves weather statistics generated by the WeatherService.
     *
     * @return \Illuminate\Http\JsonResponse Weather statistics data in JSON format.
     *                                     The specific format depends on WeatherService implementation.
     */
    public function getWeatherStatistics()
    {
        $statistics = $this->weatherService->getWeatherStatistics();
        return response()->json($statistics);
    }

    public function initLiveWeatherUpdate()
    {
        return WebSocket::call('liveWeatherUpdate', function ($cities) {
            // Handle live weather updates for the provided cities
        });
    }

    public function liveRadarView()
    {
        return view('live-radar');
    }
}
