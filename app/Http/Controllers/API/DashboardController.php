<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ParkingSession;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'weekly');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $today = Carbon::today();
        $activeCars = ParkingSession::query()->whereNull('exit_time')->count();

        $todayRevenue = ParkingSession::query()->whereDate('exit_time', $today)
            ->sum('amount');

        $totalRevenue = ParkingSession::query()->sum('amount');

        // Earnings breakdown
        $earnings = [];
        if ($filter === 'weekly') {
            $earnings = collect(range(0, 6))->map(function ($dayOffset) use ($today) {
                $date = $today->copy()->subDays(6 - $dayOffset);
                return ParkingSession::query()->whereDate('exit_time', $date)
                    ->sum('amount');
            });
        } elseif ($filter === 'monthly') {
            $earnings = collect(range(1, 12))->map(function ($month) {
                return ParkingSession::query()->whereMonth('exit_time', $month)
                    ->whereYear('exit_time', now()->year)
                    ->sum('amount');
            });
        } elseif ($filter === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            $days = $start->diffInDays($end);
            $earnings = collect(range(0, $days))->map(function ($i) use ($start) {
                $date = $start->copy()->addDays($i);
                return ParkingSession::query()->whereDate('exit_time', $date)
                    ->sum('amount');
            });
        }

        return response()->json([
            'active_cars' => $activeCars,
            'today_revenue' => $todayRevenue,
            'total_revenue' => $totalRevenue,
            'earnings' => $earnings,
        ]);
    }
}

