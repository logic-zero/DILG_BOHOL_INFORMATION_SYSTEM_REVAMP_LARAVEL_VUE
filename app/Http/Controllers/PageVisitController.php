<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PageVisit;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PageVisitController extends Controller
{
    // Show total visit count with caching
    public function getVisitCount()
    {
        // Cache total visit count for 5 minutes
        $totalVisits = Cache::remember('page_visit_count', 300, function () {
            return PageVisit::count();
        });

        $todaysVisits = PageVisit::whereDate('created_at', Carbon::today())->count();

        $yesterdaysVisits = PageVisit::whereDate('created_at', Carbon::yesterday())->count();

        return response()->json([
            'total_visits' => $totalVisits,
            'todays_visits' => $todaysVisits,
            'yesterdays_visits' => $yesterdaysVisits
        ]);
    }

    // Track page visit and prevent duplicate entries within 10 seconds
    public function trackVisit(Request $request)
    {
        $ip = $request->ip();

        // Check if this IP recorded a visit in the last 10 seconds
        $recentVisit = PageVisit::where('ip_address', $ip)
            ->where('created_at', '>=', Carbon::now()->subSeconds(10))
            ->exists();

        if (!$recentVisit) {
            PageVisit::create(['ip_address' => $ip]);

            // Clear cache to update visit count
            Cache::forget('page_visit_count');
        }

        return response()->json(['message' => 'Visit recorded']);
    }
}
