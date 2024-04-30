<?php

namespace App\Providers;

use DateTime;
use Traits\HeadersTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class RequestlogServiceProvider extends ServiceProvider
{
    use HeadersTrait;
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        dd(56);
    }

    /**
     * Bootstrap services.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function boot(Request $request)
    {
        // dd($request);
        $logDetails = $this->prepareLogDetails($request);
        $this->sendLogToApi($logDetails);
    }

    /**
     * Prepare log details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function prepareLogDetails(Request $request)
    {
        $headersObject = collect($request->header())->map(function ($value, $key) {
            return $value[0];
        });

        $response = Http::get(env('APP_URL'));
      
        // Log the details
        return [
            "Protocol" => $request->server("SERVER_PROTOCOL"),
            "Request_url" => $request->fullUrl(),
            "Time" => $time = (new DateTime())->format("F jS Y, h:i:s"),
            "Hostname" => gethostname(),
            "Method" => $request->method(),
            "Path" => $request->path(),
            "Status_code" => $response->getStatusCode(),
            "Status_text" => $response->getReasonPhrase(),
            "IP_Address" => $request->ip(),
            "Memory_usage" => round(memory_get_usage(true) / (1024 * 1024), 2) . " MB",
            "User-agent" => $request->header("user-agent"),
            "HEADERS" => $headersObject,
        ];
    }

    /**
     * Send log to API.
     *
     * @param  array  $logDetails
     * @return void
     */
    private function sendLogToApi($logDetails)
    {
        $body = [
            "request_user_agent" => $logDetails["User-agent"],
            "request_host" => $logDetails["HEADERS"]->get('host'),
            "request_url" => $logDetails["Request_url"],
            "request_method" => $logDetails["Method"],
            "status_code" => $logDetails["Status_code"],
            "status_message" => $logDetails["Status_text"],
            "requested_at" => $logDetails["Time"],
            "request_ip" => $logDetails["IP_Address"],
            "response_message" => "Project created successfully",
            "protocol" => $logDetails["Protocol"],
            "payload" => "Payload",
            "tag" => env('TAG'),
            "meta" => [
                "Hostname" => gethostname(),
                "Path" => $logDetails["Path"],
                "Memory_usage" => $logDetails["Memory_usage"],
                "HEADERS" => $logDetails["HEADERS"]->toArray(),
            ],
        ];
        $response = $this->processApiResponse("/api/logs", $body);
    }
}
