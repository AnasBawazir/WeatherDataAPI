<?php

namespace App\Console\Commands;

use App\Services\WeatherService;
use Exception;
use Illuminate\Console\Command;

class FetchWeatherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches weather data for all cities specified';

    /**
     * Execute the console command.
     *
     * @param WeatherService $weatherService
     * @return int
     */
    public function handle(WeatherService $weatherService)
    {
        $cities = config('app.cities');
        $cityNames = array_keys($cities);

        try {
            $weatherData = $weatherService->getBulkWeather($cityNames);

            $this->info('Fetched weather data');
            return $weatherData;
        } catch (Exception $e) {
            $this->error('Failed to fetch weather data: ' . $e->getMessage());
            return 1; // Return a non-zero exit code to indicate failure
        }

        return 0; // Return a zero exit code to indicate success
    }
}
