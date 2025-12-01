<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the portfolio items.
     */
    public function index(): JsonResponse
    {
        try {
            $portfolio = Portfolio::orderBy('date', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $portfolio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du portfolio'
            ], 500);
        }
    }

    /**
     * Display the specified portfolio item.
     */
    public function show($id): JsonResponse
    {
        try {
            $portfolio = Portfolio::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $portfolio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Élément du portfolio non trouvé'
            ], 404);
        }
    }

    /**
     * Get portfolio items by category.
     */
    public function byCategory($category): JsonResponse
    {
        try {
            $portfolio = Portfolio::where('category', $category)
                ->orderBy('date', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $portfolio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du portfolio par catégorie'
            ], 500);
        }
    }

    /**
     * Get featured portfolio items.
     */
    public function featured(): JsonResponse
    {
        try {
            $portfolio = Portfolio::where('featured', true)
                ->orderBy('date', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $portfolio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des éléments en vedette'
            ], 500);
        }
    }
}