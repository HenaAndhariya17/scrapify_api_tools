<?php
namespace Scrapify\ApiTools;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Exception;

class ApiService
{
    /**
     * Call external API.
     *
     * @param string $route
     * @param string $method
     * @param array $payload
     * @param string|null $authType
     * @param array $authData
     * @param array $headers
     * @return Response
     * @throws Exception
     */
    public function callApi(
        string $route,
        string $method,
        array $payload = [],
        ?string $authType = null,
        array $authData = [],
        array $headers = []
    ): Response {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '1G');

        try {
            // Filter and prepare clean headers
            $cleanHeaders = [];
            foreach ($headers as $key => $value) {
                // Laravel adds 0: "application/json" style headers — skip those
                if (!is_numeric($key)) {
                    $cleanHeaders[$key] = $value[0] ?? $value;
                }
            }

            // Default header to avoid being blocked
            $cleanHeaders = array_merge([
                'User-Agent' => 'Scrapify/1.0',
                'Accept' => 'application/json',
            ], $cleanHeaders);

            $http = Http::withHeaders($cleanHeaders);

            // Authentication
            if ($authType === 'Basic' && isset($authData['username'], $authData['password'])) {
                $http = $http->withBasicAuth($authData['username'], $authData['password']);
            } elseif ($authType === 'Bearer' && isset($authData['token'])) {
                $http = $http->withToken($authData['token']);
            }

            return match (strtoupper($method)) {
                'GET'    => $http->get($route, $payload),
                'POST'   => $http->post($route, $payload),
                'PUT'    => $http->put($route, $payload),
                'DELETE' => $http->delete($route, $payload),
                default  => throw new Exception("Invalid HTTP method: {$method}")
            };
        } catch (Exception $e) {
            throw new Exception("API call failed: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
