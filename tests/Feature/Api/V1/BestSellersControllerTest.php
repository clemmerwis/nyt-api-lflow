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
            'title' => '#GIRLBOSS',
            'description' => 'An online fashion retailer traces her path to success.',
            'contributor' => 'by Sophia Amoruso',
            'author' => 'Sophia Amoruso',
            'contributor_note' => '',
            'price' => 0,
            'age_group' => '',
            'publisher' => 'Portfolio/Penguin/Putnam',
            'isbns' => [
                [
                    'isbn10' => '039916927X',
                    'isbn13' => '9780399169274'
                ]
            ],
            'ranks_history' => [
                [
                    'primary_isbn10' => '1591847931',
                    'primary_isbn13' => '9781591847939',
                    'rank' => 8,
                    'list_name' => 'Business Books',
                    'display_name' => 'Business',
                    'published_date' => '2016-03-13',
                    'bestsellers_date' => '2016-02-27',
                    'weeks_on_list' => 0,
                    'ranks_last_week' => null,
                    'asterisk' => 0,
                    'dagger' => 0
                ]
            ],
            'reviews' => [
                [
                    'book_review_link' => '',
                    'first_chapter_link' => '',
                    'sunday_review_link' => '',
                    'article_chapter_link' => ''
                ]
            ]
        ];
    }

    /** @test */
    public function all_parameters_are_optional(): void
    {
        $expectedResponse = [
            'status' => 'OK',
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
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
        $filters = ['author' => 'Sophia Amoruso'];

        $expectedResponse = [
            'status' => 'OK',
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
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
        $filters = ['title' => '#GIRLBOSS'];

        $expectedResponse = [
            'status' => 'OK',
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
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
        $filters = ['isbn' => '9781591847939'];

        $expectedResponse = [
            'status' => 'OK',
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
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
        // Request with array of ISBNs
        $requestFilters = [
            'isbn' => ['039916927X', '1591847931']
        ];

        // Service will receive semicolon-separated string after FormRequest processing
        $expectedServiceFilters = [
            'isbn' => '039916927X;1591847931'
        ];

        $expectedResponse = [
            'status' => 'OK',
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
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
    public function can_use_all_filters_together(): void
    {
        $requestFilters = [
            'author' => 'Sophia Amoruso',
            'isbn' => ['039916927X', '1591847931'],
            'title' => '#GIRLBOSS',
            'offset' => 20
        ];

        $expectedServiceFilters = [
            'author' => 'Sophia Amoruso',
            'isbn' => '039916927X;1591847931',
            'title' => '#GIRLBOSS',
            'offset' => 20
        ];

        $expectedResponse = [
            'status' => 'OK',
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
            'num_results' => 3,
            'results' => [$this->getSampleBestSellerData()]
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
            'isbn' => ['039916927X', '1591847931']
        ];

        // What the service should receive after FormRequest processing
        $expectedServiceFilters = [
            'isbn' => '039916927X;1591847931'
        ];

        $expectedResponse = [
            'status' => 'OK',
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
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
    public function offset_zero_is_valid(): void
    {
        $filters = ['offset' => 0];

        $expectedResponse = [
            'status' => 'OK',
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
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
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
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
            'copyright' => 'Copyright (c) 2019 The New York Times Company.  All Rights Reserved.',
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
