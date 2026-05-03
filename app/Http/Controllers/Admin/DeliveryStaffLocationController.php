<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeliveryStaffLocationMapService;
use Illuminate\Http\JsonResponse;

class DeliveryStaffLocationController extends Controller
{
    public function __invoke(DeliveryStaffLocationMapService $mapService): JsonResponse
    {
        $markers = $mapService->markers();

        return response()->json([
            'markers' => $markers,
            'stats' => $mapService->stats($markers),
            'refreshed_at' => now()->toIso8601String(),
        ]);
    }
}
