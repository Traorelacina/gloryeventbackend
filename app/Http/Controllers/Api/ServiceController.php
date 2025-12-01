<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index(): JsonResponse
    {
        try {
            $services = Service::all();
            return response()->json([
                'success' => true,
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des services',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified service.
     */
    public function show($slug): JsonResponse
    {
        try {
            $service = Service::where('slug', $slug)->firstOrFail();
            
            return response()->json([
                'success' => true,
                'data' => $service
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service non trouvé'
            ], 404);
        }
    }

    /**
     * Get services by category.
     */
    public function byCategory($category): JsonResponse
    {
        try {
            $services = Service::where('category', $category)->get();
            
            return response()->json([
                'success' => true,
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des services'
            ], 500);
        }
    }

    /**
     * Get featured services.
     */
    public function featured(): JsonResponse
    {
        try {
            $services = Service::where('featured', true)->get();
            
            return response()->json([
                'success' => true,
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des services en vedette'
            ], 500);
        }
    }
}