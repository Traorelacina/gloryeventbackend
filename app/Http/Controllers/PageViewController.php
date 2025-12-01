<?php
// app/Http/Controllers/PageViewController.php

namespace App\Http\Controllers;

use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PageViewController extends Controller
{
    public function trackView(Request $request)
    {
        $validated = $request->validate([
            'page_name' => 'required|string'
        ]);

        $ipAddress = $request->ip();
        $pageName = $validated['page_name'];
        
        // Vérifier si une vue existe déjà pour cette IP et cette page dans les 10 dernières minutes
        $recentView = PageView::where('ip_address', $ipAddress)
            ->where('page_name', $pageName)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();

        // Si une vue récente existe, ne pas enregistrer
        if ($recentView) {
            return response()->json([
                'success' => false,
                'message' => 'Vue déjà enregistrée récemment',
                'next_allowed' => $recentView->created_at->addMinutes(10)->diffForHumans()
            ]);
        }

        // Enregistrer la nouvelle vue
        $pageView = PageView::create([
            'page_name' => $pageName,
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vue enregistrée avec succès'
        ]);
    }

    public function getStatistics()
    {
        // Dates pour les calculs
        $today = Carbon::today()->format('Y-m-d');
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Vues par jour (30 derniers jours)
        $dailyViews = PageView::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as views')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        // Vues par mois (12 derniers mois)
        $monthlyViews = PageView::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as views')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        // Vues par année
        $yearlyViews = PageView::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as views')
        )
        ->groupBy('year')
        ->orderBy('year')
        ->get();

        // Vues par page
        $pageViews = PageView::select(
            'page_name',
            DB::raw('COUNT(*) as views')
        )
        ->groupBy('page_name')
        ->orderBy('views', 'desc')
        ->get();

        // Calculs supplémentaires
        $todayViews = PageView::whereDate('created_at', $today)->count();
        $monthViews = PageView::whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->count();
        $yearViews = PageView::whereYear('created_at', $currentYear)->count();

        return response()->json([
            'daily_views' => $dailyViews,
            'monthly_views' => $monthlyViews,
            'yearly_views' => $yearlyViews,
            'page_views' => $pageViews,
            'total_views' => PageView::count(),
            'today_views' => $todayViews,
            'month_views' => $monthViews,
            'year_views' => $yearViews
        ]);
    }

    public function getDashboardStats()
    {
        $today = now()->format('Y-m-d');
        $month = now()->month;
        $year = now()->year;

        return response()->json([
            'total_views' => PageView::count(),
            'today_views' => PageView::whereDate('created_at', $today)->count(),
            'month_views' => PageView::whereMonth('created_at', $month)
                                    ->whereYear('created_at', $year)
                                    ->count(),
            'year_views' => PageView::whereYear('created_at', $year)->count(),
            'daily_views' => PageView::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as views')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'page_views' => PageView::select(
                    'page_name',
                    DB::raw('COUNT(*) as views')
                )
                ->groupBy('page_name')
                ->orderBy('views', 'desc')
                ->get()
        ]);
    }
}