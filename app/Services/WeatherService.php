<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * The prefix used for cache keys when storing weather data.
     *
     * @var string
     */
    protected $cachePrefix = 'weather_';

    /**
     * Retrieves the current weather data for the specified city.
     *
     * @param string $city The name of the city for which weather data should be retrieved.
     * @return array The weather data for the specified city.
     */
    public function getWeatherForCity(string $city)
    {
        $cacheKey = $this->cachePrefix . strtolower($city);
        $cachedData = Cache::get($cacheKey);

        // If the weather data is cached, return the cached data
        if ($cachedData) {
            return $cachedData;
        }

        $weatherData = $this->fetchWeatherData(strtolower($city));

        // If an error occurred during the API call, return the error response
        if (isset($weatherData['error'])) {
            return $weatherData;
        }

        return $weatherData;
    }

    /**
     * Retrieves the current weather data for multiple cities in a single request.
     *
     * @param array $cities An array of city names for which weather data should be retrieved.
     * @return array An associative array containing the weather data for each city, with the city name as the key.
     */
    public function getBulkWeather(array $cities)
    {
        $weatherData = [];
        foreach ($cities as $city) {
            $weatherData[$city] = $this->getWeatherForCity($city);
        }

        return $weatherData;
    }

    /**
     * Retrieves aggregated statistics for the weather data across all configured cities.
     *
     * @return array An array containing the following statistics:
     *               - temperatures: Average, highest, and lowest temperatures across all cities.
     *               - relative_humidity_2m: Average, highest, and lowest relative humidity at 2 meters across all cities.
     *               - wind_speed: Average, highest, and lowest wind speed across all cities.
     */
    public function getWeatherStatistics()
    {
        $cities = config('app.cities');
        $weatherData = $this->getBulkWeather(array_keys($cities));

        $statistics = [
            'temperatures' => [],
            'relative_humidity_2m' => [],
            'wind_speed' => [],
        ];

        foreach ($weatherData as $city => $cityData) {
            // Check if the weather data is valid and contains the 'current' key
            if (!isset($cityData['error']) && isset($cityData['current'])) {
                $currentData = $cityData['current'];

                // Populate the statistics for temperature
                if (isset($currentData['temperature_2m'])) {
                    $statistics['temperatures'][] = [
                        'city' => $city,
                        'value' => $currentData['temperature_2m'],
                    ];
                }

                // Populate the statistics for relative humidity
                if (isset($currentData['relative_humidity_2m'])) {
                    $statistics['relative_humidity_2m'][] = [
                        'city' => $city,
                        'value' => $currentData['relative_humidity_2m'],
                    ];
                }

                // Populate the statistics for wind speed
                if (isset($currentData['wind_speed_10m'])) {
                    $statistics['wind_speed'][] = [
                        'city' => $city,
                        'value' => $currentData['wind_speed_10m'],
                    ];
                }
            }
        }

        // Calculate the average, highest, and lowest values for each statistic
        $statistics = array_map(function ($attribute) use ($cities) {
            $values = array_column($attribute, 'value');
            $cityNames = array_column($attribute, 'city');

            $average = array_sum($values) / count($values);
            $highest = max($values);
            $lowest = min($values);

            $averageCity = $cityNames[array_search($average, $values)];
            $highestCity = $cityNames[array_search($highest, $values)];
            $lowestCity = $cityNames[array_search($lowest, $values)];

            return [
                'average' => "$averageCity,$average",
                'highest' => "$highestCity,$highest",
                'lowest' => "$lowestCity,$lowest",
            ];
        }, $statistics);

        return $statistics;
    }

    /**
     * Fetches the weather data for the specified city from the Open-Meteo API.
     *
     * @param string $city The name of the city for which weather data should be fetched.
     * @return array The weather data fetched from the API, or an error array if an exception occurs.
     */
    public function fetchWeatherData($city)
    {
        try {
            // Retrieve the coordinates for the specified city from the application configuration
            $coordinates = config('app.cities.' . $city);
            if (!$coordinates) {
                throw new Exception("Coordinates not found for city: $city");
            }

            // Make a GET request to the Open-Meteo API using the coordinates
            $response = Http::get('https://api.open-meteo.com/v1/forecast?latitude=' . $coordinates['lat'] . '&longitude=' . $coordinates['lng'] . '&current=temperature_2m,relative_humidity_2m,cloud_cover,apparent_temperature,wind_speed_10m,wind_direction_10m,rain&timezone=Asia/Riyadh');

            // If the API response is successful, cache the fetched data and return it
            if ($response->successful()) {
                Cache::put($this->cachePrefix . $city, $response->json(), 60 * 15);
                return $response->json();
            }

            // If the API response is not successful, throw an exception with the API response body
            throw new Exception("Failed to fetch weather data for city: $city. Error: " . $response->body());
        } catch (Exception $e) {
            // Handle exceptions by logging the error and returning an error array
            Log::error('Error fetching weather data: ' . $e->getMessage());

            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }
}
