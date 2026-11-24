<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->latest('id')
            ->paginate(25);

        return view('activity-logs.index', compact('logs'));
    }
}
