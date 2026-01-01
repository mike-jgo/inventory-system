<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject']);

        // Search functionality
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('causer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('subject_id', 'like', "%{$search}%");
            });
        }

        // Action filter
        if ($action = $request->input('action')) {
            $query->where('description', $action);
        }

        // Entity filter - match by class basename to support any namespace
        if ($entity = $request->input('entity')) {
            $query->whereRaw('subject_type LIKE ?', ['%\\' . $entity]);
        }

        // Date range filter
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $activities = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Get unique entities and actions for filter options
        $entities = Activity::select('subject_type')
            ->distinct()
            ->whereNotNull('subject_type')
            ->pluck('subject_type')
            ->map(fn($type) => class_basename($type)) // Extract just the class name from any namespace
            ->sort()
            ->values();

        $actions = Activity::select('description')
            ->distinct()
            ->whereIn('description', ['created', 'updated', 'deleted', 'restored'])
            ->pluck('description')
            ->sort()
            ->values();

        return Inertia::render('ActivityLog/Index', [
            'activities' => $activities,
            'filters' => [
                'search' => $request->input('search'),
                'action' => $request->input('action'),
                'entity' => $request->input('entity'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ],
            'filterOptions' => [
                'entities' => $entities,
                'actions' => $actions,
            ],
        ]);
    }

    public function saveRemarks(Request $request, $id)
    {
        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $activity = Activity::findOrFail($id);
        $oldRemarks = $activity->remarks;
        $newRemarks = $validated['remarks'];

        $activity->remarks = $newRemarks;
        $activity->save();

        // Log the remarks change as an activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($activity)
            ->withProperties([
                'old' => ['remarks' => $oldRemarks],
                'attributes' => ['remarks' => $newRemarks],
                'activity_log_id' => $activity->id,
            ])
            ->log('Updated remarks on activity log');

        return back()->with('success', 'Remarks saved successfully.');
    }
}
