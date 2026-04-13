<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(50);

        return Inertia::render('ActivityLog/Index', [
            'logs' => $logs,
        ]);
    }
}
