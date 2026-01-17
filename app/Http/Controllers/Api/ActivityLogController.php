<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ActivityLogController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of activity logs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $logs = $this->activityLogService->getLogs($request);

            return response()->json([
                'success' => true,
                'data' => ActivityLogResource::collection($logs->items()),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                    'last_page' => $logs->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity logs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export activity logs.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $logs = $this->activityLogService->exportLogs($request);

            return response()->json([
                'success' => true,
                'data' => ActivityLogResource::collection($logs),
                'meta' => [
                    'total' => $logs->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export activity logs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all activity logs.
     * Hidden feature - Only accessible by Super Admin.
     *
     * @return JsonResponse
     */
    public function clear(): JsonResponse
    {
        try {
            // Check if user is Super Admin
            $user = auth()->user();
            if (!$user || !$user->hasRole('Super Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Super Admin can clear activity logs.',
                ], 403);
            }

            $deletedCount = $this->activityLogService->clearLogs();

            return response()->json([
                'success' => true,
                'message' => "Successfully cleared {$deletedCount} activity log(s).",
                'data' => [
                    'deleted_count' => $deletedCount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear activity logs: ' . $e->getMessage(),
            ], 500);
        }
    }
}

