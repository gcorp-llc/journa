<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class CityService
{
    protected int $maxRetries = 3;
    protected string $citiesApiUrl = 'https://api.nobat.ir/cities';

    public function fetchCities(): Collection
    {
        try {
            $response = Http::retry($this->maxRetries, 1000)->get($this->citiesApiUrl);
            if ($response->successful() && is_array($response->json())) {
                return collect($response->json());
            }
            Log::warning('CityService: Invalid response from cities API', ['response' => $response->body()]);
            return collect();
        } catch (\Exception $e) {
            Log::error('CityService: Exception fetching cities', ['exception' => $e]);
            return collect();
        }
    }
}

