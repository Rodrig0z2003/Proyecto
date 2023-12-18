<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WeatherController extends Controller
{
    private $API_KEY = '98117bb23fd5582ab288c0c53da75339';
    private $ENDPOINT = 'https://api.openweathermap.org/data/2.5/weather?q={SEARCH}&units=metric&appid={TOKEN}';

    public function getCityWeather(Request $request) {
        $city = $request->route('city');
        $url = str_replace('{SEARCH}', $city, $this->ENDPOINT);
        $url = str_replace('{TOKEN}', $this->API_KEY, $url);

        $response = file_get_contents($url);

        return response()->json(json_decode($response));
    }
}
