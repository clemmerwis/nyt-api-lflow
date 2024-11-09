<?php

namespace Tests\Feature\Api\V1;

use App\Services\NYTBooksService;
use Tests\TestCase;
use Mockery\MockInterface;

/** Key Test Cases
* all_parameters_are_optional()
* can_search_by_author_only()
* can_search_by_title_only()
* can_search_by_single_isbn()
* can_search_by_multiple_isbns()
* can_use_all_filters_together()
* validates_isbn_must_be_numeric()
* validates_isbn_length()
* converts_isbn_array_to_semicolon_separated_string()
* accepts_comma_separated_isbn_string_and_converts_to_semicolon()
* offset_zero_is_valid()
* pagination_works_with_larger_offset()
* validates_offset_multiple_of_twenty()
* returns_empty_results_when_no_matches()
* handles_api_errors()
*/
class BestSellersControllerTest extends TestCase
{
    protected MockInterface $mock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock = $this->mock(NYTBooksService::class);
    }

    private function getSampleBestSellerData(): array
    {
        return [
            'title' => 'The Test Novel',
            'description' => 'A compelling story about software testing.',
            'author' => 'Jane Developer',
            'contributor' => 'by Jane Developer',
            'publisher' => 'Tech Publishing',
            'ranks_history' => [
                [
                    'rank' => 1,
                    'list_name' => 'Hardcover Fiction',
                    'published_date' => '2024-01-01',
                    'weeks_on_list' => 5
                ]
            ]
        ];
    }

    /** @test */
    public function all_parameters_are_optional(): void
    {
        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 28970,
            'results' => [$this->getSampleBestSellerData()]
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with([])
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'));

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function can_search_by_author_only(): void
    {
        $filters = ['author' => 'Jane Developer'];

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 5,
            'results' => [$this->getSampleBestSellerData()]
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($filters)
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'), $filters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function can_search_by_title_only(): void
    {
        $filters = ['title' => 'The Test Novel'];

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 1,
            'results' => [$this->getSampleBestSellerData()]
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($filters)
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'), $filters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function can_search_by_single_isbn(): void
    {
        $filters = ['isbn' => '1234567890'];

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 1,
            'results' => [$this->getSampleBestSellerData()]
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($filters)
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'), $filters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function can_search_by_multiple_isbns(): void
    {
        $filters = ['isbn' => '1234567890;1234567890123'];  // Semicolon-separated string

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 2,
            'results' => [
                $this->getSampleBestSellerData(),
                $this->getSampleBestSellerData()
            ]
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($filters)
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'), $filters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function can_use_all_filters_together(): void
    {
        $filters = [
            'author' => 'Jane Developer',
            'isbn' => '1234567890;1234567890123',
            'title' => 'The Test Novel',
            'offset' => 20
        ];

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 3,
            'results' => [$this->getSampleBestSellerData()]
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($filters)
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'), $filters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function validates_isbn_must_be_numeric(): void
    {
        $response = $this->postJson(route('api.v1.best-sellers'), [
            'isbn' => '123abc4567'  // Single non-numeric ISBN
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);

        $response = $this->postJson(route('api.v1.best-sellers'), [
            'isbn' => '1234567890;123abc4567'  // Multiple ISBNs with non-numeric
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);
    }

    /** @test */
    public function validates_isbn_length(): void
    {
        $response = $this->postJson(route('api.v1.best-sellers'), [
            'isbn' => '123'  // Too short
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);

        $response = $this->postJson(route('api.v1.best-sellers'), [
            'isbn' => '12345678901234'  // Too long
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);

        $response = $this->postJson(route('api.v1.best-sellers'), [
            'isbn' => '123;12345678901234'  // Multiple invalid lengths
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['isbn']);
    }

    /** @test */
    public function converts_isbn_array_to_semicolon_separated_string(): void
    {
        // Input from client as array
        $requestFilters = [
            'isbn' => ['1234567890', '1234567890123']
        ];

        // What the service should receive after FormRequest processing
        $expectedServiceFilters = [
            'isbn' => '1234567890;1234567890123'
        ];

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 2,
            'results' => [
                $this->getSampleBestSellerData(),
                $this->getSampleBestSellerData()
            ]
        ];

        // Verify the service receives the converted string format
        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($expectedServiceFilters)
            ->andReturn($expectedResponse);

        // Send request with array format
        $response = $this->postJson(route('api.v1.best-sellers'), $requestFilters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function accepts_comma_separated_isbn_string_and_converts_to_semicolon(): void
    {
        // Input with comma-separated ISBNs
        $requestFilters = [
            'isbn' => '1234567890,1234567890123'
        ];

        // What the service should receive after FormRequest processing
        $expectedServiceFilters = [
            'isbn' => '1234567890;1234567890123'
        ];

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 2,
            'results' => [
                $this->getSampleBestSellerData(),
                $this->getSampleBestSellerData()
            ]
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($expectedServiceFilters)
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'), $requestFilters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function offset_zero_is_valid(): void
    {
        $filters = ['offset' => 0];

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 100,
            'results' => array_fill(0, 20, $this->getSampleBestSellerData()) // First 20 results
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($filters)
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'), $filters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function pagination_works_with_larger_offset(): void
    {
        $filters = ['offset' => 40];  // Third page

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 45,  // Total results across all pages
            'results' => array_fill(0, 5, $this->getSampleBestSellerData()) // Remaining 5 results
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($filters)
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'), $filters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function validates_offset_multiple_of_twenty(): void
    {
        $response = $this->postJson(route('api.v1.best-sellers'), [
            'offset' => 25
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['offset']);
    }

    /** @test */
    public function returns_empty_results_when_no_matches(): void
    {
        $filters = [
            'author' => 'Nonexistent Author'
        ];

        $expectedResponse = [
            'status' => 'OK',
            'num_results' => 0,
            'results' => []
        ];

        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->with($filters)
            ->andReturn($expectedResponse);

        $response = $this->postJson(route('api.v1.best-sellers'), $filters);

        $response->assertOk()
            ->assertJson($expectedResponse);
    }

    /** @test */
    public function handles_api_errors(): void
    {
        $this->mock->shouldReceive('getBestSellersHistory')
            ->once()
            ->andThrow(new \Exception('API Error', 500));

        $response = $this->postJson(route('api.v1.best-sellers'));

        $response->assertStatus(500)
            ->assertJson(['error' => 'API Error']);
    }
}
