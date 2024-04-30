<?php

namespace Traits;

use Illuminate\Support\Facades\Http;

trait HeadersTrait
{

    private $baseURL = 'https://api.bugatlas.com/v1';

    /**
     * Process API response.
     *
     * @param  string  $endPoint
     * @param  array  $body
     * @return mixed
     */
    public function processApiResponse($endPoint, $body)
    {
        return Http::withHeaders($this->getApiHeaders())->post($this->baseURL . $endPoint, $body);
    }


    /**
     * Get API headers.
     *
     * @return array
     */
    private function getApiHeaders()
    {
        return [
            'api_key' => env('API_KEY'),
            'secret_key' => env('SECRET_KEY'),
            'Content-Type' => 'application/json'
        ];
    }
}

