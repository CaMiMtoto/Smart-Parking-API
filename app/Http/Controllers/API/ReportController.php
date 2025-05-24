<?php

namespace App\Http\Controllers\API;

use App\Models\ParkingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController
{
    public function report(Request $request)
    {
        $from = $request->query('from_date');
        $to = $request->query('to_date');

        $query = ParkingSession::query();

        if ($from) {
            $query->whereDate('entry_time', '>=', $from);
        }
        if ($to) {
            $query->whereDate('entry_time', '<=', $to);
        }

        $totalCars = $query->count();

        $completedSessions = (clone $query)->where('status', 'completed');

        $totalCompleted = $completedSessions->count();

        $totalAmount = $completedSessions->sum('amount');

        $totalDurationMinutes = $completedSessions->sum('duration_minutes');

        $averageDurationMinutes = $totalCompleted > 0 ? round($totalDurationMinutes / $totalCompleted, 2) : 0;

        // Cars currently parked (active)
        $activeCars = (clone $query)->where('status', 'active')->count();

        return response()->json([
            'total_cars' => $totalCars,
            'active_cars' => $activeCars,
            'total_completed' => $totalCompleted,
            'total_amount' => round($totalAmount, 2),
            'total_duration_minutes' => $totalDurationMinutes,
            'average_duration_minutes' => $averageDurationMinutes,
        ]);
    }

}
