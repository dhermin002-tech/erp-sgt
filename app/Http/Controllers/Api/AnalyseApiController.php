<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyseTaches;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyseApiController extends Controller
{
    public function index(Request $request, AnalyseTaches $analyse): JsonResponse
    {
        $periode = $request->validate([
            'du' => ['sometimes', 'date_format:Y-m-d'],
            'au' => ['sometimes', 'date_format:Y-m-d', ...($request->has('du') ? ['after_or_equal:du'] : [])],
        ]);

        return response()->json(['data' => $analyse->synthese($request->user(), $periode)]);
    }
}
