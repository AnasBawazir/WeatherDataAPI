<?php

namespace Tests\Feature;

use App\Services\WeatherService;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{

    /** @test */
    public function it_returns_cached_weather_data_for_city()
    {
        // Arrange
        $city = 'Madina';
        $cachedData = [
            'latitude' => 24.5,
            'longitude' => 39.6,
        ];
        Cache::put('weather_' . strtolower($city), $cachedData);

        $weatherService = new WeatherService();

        // Act
        $weatherData = $weatherService->getWeatherForCity($city);

        // Assert
        $this->assertEquals($cachedData, $weatherData);
    }

    /** @test */
    public function it_fetches_weather_data_from_api_when_not_cached()
    {
        // Arrange
        $city = 'Jeddah';
        $expectedLatitude = 21.5;


        $weatherService = new WeatherService();
        // Act
        $weatherData = $weatherService->getWeatherForCity($city);

        // Assert
        $this->assertEquals($expectedLatitude, $weatherData['latitude']);
    }

    /** @test */
    public function it_returns_bulk_weather_data_for_cities()
    {
        // Arrange
        $cities = ['Riyadh', 'Jeddah', 'Makkah'];
        $weatherData = [
            'Riyadh' => [
                'latitude' => 24.75,
                'longitude' => 46.75,
                // ... (the rest of the Riyadh data)
            ],
            'Jeddah' => [
                'latitude' => 21.5,
                'longitude' => 39.200005,
                // ... (the rest of the Jeddah data)
            ],
            'Makkah' => [
                'latitude' => 21.4,
                'longitude' => 39.800003,
                // ... (the rest of the Makkah data)
            ],
        ];

        $weatherService = new WeatherService();
        // Act
        $bulkWeatherData = $weatherService->getBulkWeather($cities);

        // Assert
        $this->assertEquals($weatherData, $bulkWeatherData);
    }

    /** @test */
    public function it_calculates_weather_statistics_correctly()
    {
        // Arrange
        $expectedStatistics = [
            'temperatures' => [
                'average' => 'jeddah,23.1875',
                'highest' => 'makkah,29',
                'lowest' => 'abha,16.1',
            ],
            'relative_humidity_2m' => [
                'average' => 'jeddah,59.375',
                'highest' => 'dammam,88',
                'lowest' => 'madina,11',
            ],
            'wind_speed' => [
                'average' => 'jeddah,6.55',
                'highest' => 'abha,10.7',
                'lowest' => 'riyadh,1.4',
            ],
        ];


        $weatherService = new WeatherService();

        // Act
        $statistics = $weatherService->getWeatherStatistics();

        // Assert
        $this->assertEquals($expectedStatistics, $statistics);
    }

    /** @test */
    public function it_fetches_weather_data_from_api_successfully()
    {
        // Arrange
        $city = 'Madina';
        $apiResponseLatitude = 24.5;

        $weatherService = new WeatherService();

        // Act
        $weatherData = $weatherService->fetchWeatherData(strtolower($city));

        // Assert
        $this->assertEquals($apiResponseLatitude, $weatherData['latitude']);
    }

    /** @test */
    public function it_returns_error_when_city_not_found_in_configuration()
    {
        // Arrange
        $city = 'InvalidCity';
        $errorResponse = [
            'error' => true,
            'message' => "Coordinates not found for city: invalidcity",
        ];

        $weatherService = new WeatherService();

        // Act
        $weatherData = $weatherService->getWeatherForCity($city);

        // Assert
        $this->assertEquals($errorResponse, $weatherData);
    }

    /** @test */
    public function it_returns_error_when_api_call_fails()
    {
        // Arrange
        $city = 'Makkah';
        $errorResponse = [
            'error' => true,
            'message' => 'Failed to fetch weather data for city: Makkah. Error: API Error',
        ];
        $weatherService = new WeatherService();
        // Act
        $weatherData = $weatherService->fetchWeatherData($city);

        // Assert
        $this->assertEquals($errorResponse, $weatherData);
    }

}
