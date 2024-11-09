<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Factory as HttpFactory;

class NYTBooksService
{
    private string $apiKey;
    private HttpFactory $http;
    private string $baseUrl = 'https://api.nytimes.com/svc/books/v3';

    public function __construct(string $apiKey, HttpFactory $http)
    {
        $this->apiKey = $apiKey;
        $this->http = $http;
    }

    /**
     * Get best sellers history based on provided filters
     *
     * @param array{
     *   author?: string,
     *   isbn?: string,    // Semicolon-separated string of ISBNs
     *   title?: string,
     *   offset?: int
     * } $filters
     * @return array{
     *   status: string,
     *   copyright: string,
     *   num_results: int,
     *   results: array
     * }
     * @throws \Exception If the API request fails
     */
    public function getBestSellersHistory(array $filters): array
    {
        $queryParams = array_filter([
            'api-key' => $this->apiKey,
            'author' => $filters['author'] ?? null,
            'title' => $filters['title'] ?? null,
            'offset' => $filters['offset'] ?? 0,
            'isbn' => $filters['isbn'] ?? null
        ]);

        $url = "{$this->baseUrl}/lists/best-sellers/history.json";

        // Add raw URL to logging to verify encoding
        Log::info('NYT API Request', [
            'url' => $url,
            'queryParams' => $queryParams,
            'example_encoded' => http_build_query($queryParams)
        ]);

        $response = $this->http->get($url, $queryParams);

        if (!$response->successful()) {
            throw new \Exception(
                "NYT API request failed: " . ($response->json('error') ?? 'Unknown error'),
                $response->status()
            );
        }

        return $response->json();
    }
}
