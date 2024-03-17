<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// GET /weather/statistics
Route::get('/weather/statistics', [\App\Http\Controllers\API\WeatherController::class, 'getWeatherStatistics']);

// GET /weather/:city
Route::get('/weather/{city}', [\App\Http\Controllers\API\WeatherController::class, 'getWeatherForCity']);

// POST /weather/bulk
Route::post('/weather/bulk', [\App\Http\Controllers\API\WeatherController::class, 'getBulkWeather']);

