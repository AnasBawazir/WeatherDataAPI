<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// WebSocket /weather/live
Route::get('/weather/live', [\App\Http\Controllers\API\WeatherController::class, 'initLiveWeatherUpdate']);

// GET /weather/live-radar
Route::get('/weather/live-radar', [\App\Http\Controllers\API\WeatherController::class, 'liveRadarView']);
