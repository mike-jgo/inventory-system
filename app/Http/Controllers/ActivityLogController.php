<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = Activity::with('causer')
        ->orderByDesc('created_at')
        ->paginate(10); // remove ['id', ...] restriction

        return Inertia::render('ActivityLog/Index', [
            'activities' => $activities,
        ]);
    }

    public function saveRemarks(Request $request, $id)
    {
        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        $activity = Activity::findOrFail($id);
        $activity->remarks = $validated['remarks'];
        $activity->save();

        return back()->with('success', 'Remarks saved successfully.');
    }
}
