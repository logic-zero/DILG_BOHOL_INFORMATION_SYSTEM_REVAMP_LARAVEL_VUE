<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\PDMUs;
use App\Models\Lgu;
use App\Models\Faq;
use App\Models\Field_Officer;
use App\Models\Bohol_Issuance;
use App\Models\Provincial_Official;
use App\Models\Citizens_Charter;
use App\Models\User;
use App\Models\PageVisit;
use App\Models\Oragnizational_Structure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = PageVisit::whereDate('created_at', today())->count();
        $thisWeek = PageVisit::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $thisMonth = PageVisit::whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count();
        $total = PageVisit::count();

        $lastTenDays = PageVisit::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(10))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        foreach ($lastTenDays as $day) {
            $labels[] = Carbon::parse($day->date)->format('M d');
            $data[] = $day->count;
        }

        return Inertia::render('Admin/Dashboard', [
            'newsStats' => [
                'approved' => News::where('status', 1)->count(),
                'pending' => News::where('status', 0)->count(),
                'total' => News::count(),
            ],
            'organizational_structure' => Oragnizational_Structure::count(),
            'pdmu' => PDMUs::count(),
            'field_officers' => Field_Officer::count(),
            'lgu' => Lgu::count(),
            'faq' => Faq::count(),
            'issuances' => Bohol_Issuance::count(),
            'prov_officials' => Provincial_Official::count(),
            'citizens_charter' => Citizens_Charter::count(),
            'users' => User::count(),
            'pageVisits' => [
                'today' => $today,
                'thisWeek' => $thisWeek,
                'thisMonth' => $thisMonth,
                'total' => $total,
                'graph' => [
                    'labels' => $labels,
                    'data' => $data,
                ],
            ],
        ]);
    }
}
