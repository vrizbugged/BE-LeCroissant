<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityLogService
{
    /**
     * Get paginated activity logs with filters.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getLogs(Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->query('per_page', 15);
        
        $query = Activity::with(['causer', 'subject'])
            ->orderByDesc('created_at');

        // Filter by causer_id (user who performed the action)
        if ($causerId = $request->query('causer_id')) {
            $query->where('causer_id', $causerId);
        }

        // Filter by subject_type (model type)
        if ($subjectType = $request->query('subject_type')) {
            $query->where('subject_type', $subjectType);
        }

        // Filter by subject_id (model ID)
        if ($subjectId = $request->query('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        // Filter by event (created, updated, deleted)
        if ($event = $request->query('event')) {
            $query->where('event', $event);
        }

        // Filter by date range
        if ($startDate = $request->query('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->query('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Search by description
        if ($search = $request->query('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        return $query->paginate($perPage);
    }

    /**
     * Export activity logs without pagination.
     *
     * @param Request $request
     * @return Collection
     */
    public function exportLogs(Request $request): Collection
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderByDesc('created_at');

        // Apply same filters as getLogs
        if ($causerId = $request->query('causer_id')) {
            $query->where('causer_id', $causerId);
        }

        if ($subjectType = $request->query('subject_type')) {
            $query->where('subject_type', $subjectType);
        }

        if ($subjectId = $request->query('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($event = $request->query('event')) {
            $query->where('event', $event);
        }

        if ($startDate = $request->query('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->query('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($search = $request->query('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        return $query->get();
    }
}

