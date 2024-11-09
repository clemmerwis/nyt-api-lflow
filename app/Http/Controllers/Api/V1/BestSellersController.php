<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BestSellersHistoryRequest;
use Illuminate\Http\JsonResponse;
use App\Services\NYTBooksService;

class BestSellersController extends Controller
{
    public function __construct(
        private NYTBooksService $booksService
    ) {}

    /**
     * Get best sellers history based on provided filters
     */
    public function __invoke(BestSellersHistoryRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Update 'isbn' with the transformed value
            if ($request->has('isbn')) {
                $validatedData['isbn'] = $request->input('isbn');
            }

            $response = $this->booksService->getBestSellersHistory($validatedData);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
