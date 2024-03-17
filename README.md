

# Laravel Weather API

This project is a Laravel-based backend application that integrates with the Open-Meteo weather API to retrieve and serve weather data for various cities in Saudi Arabia. The application implements caching and polling mechanisms to optimize API usage and provide up-to-date weather information.

Features
- Retrieve current weather data for a single city
- Retrieve current weather data for multiple cities in a single request
- Retrieve aggregated weather statistics across all cities
- Caching mechanism to store and retrieve weather data from the external API
- Polling mechanism to periodically update the weather data in the cache
- Error handling and logging
- Unit tests for comprehensive testing

## Installation

- Clone the repository with `git clone https://github.com/your-repo/WeatherDataAPI.git`
- Navigate to the project directory `cd WeatherDataAPI`
- Copy the `.env.example` file and create a new `.env` file 
- Run `composer install`
- Run `php artisan key:generate` to generate an application key
- Run `php artisan serve` to start the development server
- The application should now be accessible at http://localhost:8000

## Configuration

The application is pre-configured with the following cities and their coordinates:

- Makkah
- Madina
- Riyadh
- Jeddah
- Taif
- Dammam
- Abha
 Jazan

If you need to modify the list of cities or their coordinates, you can update the cities array in the `config/app.php` file.

## Endpoints

1. api/weather/:city

**Method**: GET

**Description**: Retrieves the current weather data for the specified city.

**Parameters**:
- `city` (string): The name of the city for which weather data should be retrieved.

**Response**:
- If the weather data for the specified city is available in the cache, the cached data will be returned.
- If the weather data is not available in the cache, it will be fetched from the Open-Meteo API, stored in the cache, and returned.

**Error Handling**:
- If the city is not found in the configured list of cities, a 404 Not Found error will be returned.
- If there is an error while fetching weather data from the Open-Meteo API, an appropriate error message will be returned.

2. api/weather/bulk

**Method**: POST

**Description**: Retrieves the current weather data for multiple cities in a single request.

**Request Body**:
- `cities` (array): An array of city names for which weather data should be retrieved.

**Response**:
- An array of weather data objects, each containing the weather information for a city.

**Error Handling**:
- If the request body is invalid or missing the required cities array, a 422 Unprocessable Entity error will be returned.
- If any city in the cities array is not found in the configured list of cities, an error message will be included in the response for that city.
- If there is an error while fetching weather data from the Open-Meteo API for any city, an error message will be included in the response for that city.

3. api/weather/statistics

**Method**: GET

**Description**: Retrieves aggregated statistics for the weather data across all configured cities.

**Response**:
+ An object containing the following statistics:
  + `temperatures`: Average, highest, and lowest temperatures across all cities.
  + `relative_humidity_2m`: Average, highest, and lowest relative humidity at 2 meters across all cities.
  + `wind_speed`: Average, highest, and lowest wind speed across all cities.

**Error Handling**:
- If there is an error while fetching weather data from the Open-Meteo API for any city, an error message will be logged, and the statistics will be calculated based on the available data.



## Caching and Polling
The application implements a caching mechanism to store and retrieve weather data from the external API. Weather data is cached for 15 minutes, reducing the number of API calls made.
To periodically update the cached weather data, a scheduled task is implemented using [Laravel's task scheduling functionality](https://laravel.com/docs/10.x/scheduling#running-the-scheduler). The `FetchWeatherCommand` is scheduled to run every 15 minutes using the following code in the `App\Console\Kernel` class:

    protected function schedule(Schedule $schedule): void
        {
            $schedule->command('weather:fetch')->everyFifteenMinutes();
        }

The `FetchWeatherCommand` fetches the weather data for all configured cities and updates the cache with the latest data.

The command can be executed alone with `php artisan weather:fetch`

## Testing
Testing
The application includes unit tests to ensure the correctness of the implemented functionality. The tests are located in the tests/Feature/WeatherServiceTest.php file and cover various scenarios, including:
- Retrieving cached weather data for a city
- Fetching weather data from the API when not cached
- Retrieving bulk weather data for multiple cities
- Calculating weather statistics correctly
- Fetching weather data from the API successfully
- Handling errors when a city is not found in the configuration
- Handling errors when the API call fails.

To run the tests, execute the following command:
`php artisan test --filter=WeatherServiceTest`
the results of the tests might be different based on the weather changes but from the logs you can ensure the correctness of the outputs.

## Design Choices

1. **Separation of Concerns**: The application follows the principle of separation of concerns by separating the business logic (weather data fetching and processing) from the HTTP request handling. The `WeatherService` class encapsulates the weather data fetching and processing logic, while the `WeatherController` class handles the HTTP requests and responses.
2. **Caching and Polling**: To optimize API usage and avoid stressing the external Open-Meteo API, the application implements a caching mechanism. Weather data is cached for a configured duration (15 minutes in the provided implementation), reducing the number of API calls made. Additionally, a scheduled task is implemented to periodically fetch and update the cached weather data.
3. **Error Handling and Logging**: The application implements error handling and logging mechanisms to handle and report errors that may occur during the fetching and processing of weather data. Errors are logged for debugging purposes, and appropriate error messages are returned to the client when necessary.
4. **Configuration Management**: The application uses Laravel's configuration management system to store and manage the list of cities and their coordinates. This configuration can be easily modified by updating the `config/app.php` file.
5. **Validation**: The `getBulkWeather` endpoint validates the incoming request body to ensure that the `cities` array is present and contains valid city names. This helps prevent potential issues and improves the overall reliability of the application.
6. **Reusability**: The `WeatherService` class is designed to be reusable and extensible. It encapsulates the logic for fetching weather data and can be easily extended to support additional features or integrate with different weather APIs.
7. **Testability**: The application includes comprehensive unit tests to ensure the correctness of the implemented functionality and to catch regressions during future development or refactoring.
