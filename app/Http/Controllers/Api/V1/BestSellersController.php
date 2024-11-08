<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BestSellersHistoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BestSellersController extends Controller
{
    /**
     * Get best sellers history based on provided filters
     */
    public function history(BestSellersHistoryRequest $request): JsonResponse
    {
        // The request is already validated by BestSellersHistoryRequest

        // TODO: Create and inject NYT API service
        // For now, return empty response
        return response()->json([
            'status' => 'Not implemented',
            'data' => []
        ]);
    }
}
