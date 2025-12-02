<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageViewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ⚠️ ROUTE TEMPORAIRE - À SUPPRIMER APRÈS UTILISATION
Route::get('/setup-admin', function () {
    try {
        $admin = App\Models\Admin::updateOrCreate(
            ['email' => 'admin@gloryevent.com'],
            [
                'name' => 'Admin Principal',
                'password' => Hash::make('Admin2024!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Admin créé avec succès !',
            'credentials' => [
                'email' => 'admin@gloryevent.com',
                'password' => 'Admin2024!'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});


// Public authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Get current user (protected)
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Public routes
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/featured', [ServiceController::class, 'featured']);
Route::get('/services/category/{category}', [ServiceController::class, 'byCategory']);
Route::get('/services/{slug}', [ServiceController::class, 'show']);
// Dans routes/api.php
Route::post('/track-view', [PageViewController::class, 'trackView']);
Route::get('/statistics', [PageViewController::class, 'getStatistics'])->middleware('auth:sanctum');
Route::get('/dashboard-stats', [PageViewController::class, 'getDashboardStats'])->middleware('auth:sanctum');
Route::get('/produits', [ProduitController::class, 'index']);
Route::get('/produits/featured', [ProduitController::class, 'featured']);
Route::get('/produits/category/{category}', [ProduitController::class, 'byCategory']);
Route::get('/produits/{slug}', [ProduitController::class, 'show']);

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    Route::post('/produits', [ProduitController::class, 'store']);
    Route::delete('/produits/{id}', [ProduitController::class, 'destroy']);
});
 Route::get('/admin/contacts', [AdminController::class, 'recentContacts']);
 Route::delete('/admin/contacts/{id}', [ContactController::class, 'destroy']);
 // Marquer comme lu/non lu
Route::put('/admin/contacts/{id}/read', [ContactController::class, 'markAsRead']);
Route::get('/portfolio', [PortfolioController::class, 'index']);
Route::get('/portfolio/featured', [PortfolioController::class, 'featured']);
Route::get('/portfolio/category/{category}', [PortfolioController::class, 'byCategory']);
Route::get('/portfolio/{id}', [PortfolioController::class, 'show']);

Route::post('/contacts', [ContactController::class, 'store']);

Route::post('/commandes', [CommandeController::class, 'store']);
Route::get('/commandes/{id}', [CommandeController::class, 'show']);

// Protected admin routes
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/recent-orders', [AdminController::class, 'recentOrders']);
    Route::get('/recent-contacts', [AdminController::class, 'recentContacts']);
    
    // Commandes management
    Route::get('/commandes', [AdminController::class, 'commandes']);
    Route::get('/commandes/{id}', [AdminController::class, 'showCommande']);
    Route::put('/commandes/{id}', [AdminController::class, 'updateCommande']);
    Route::delete('/commandes/{id}', [AdminController::class, 'destroyCommande']);
    Route::get('/commandes/{id}', [CommandeController::class, 'show']);
    
    // Product management
    // Product management - Utilisez ProduitController au lieu d'AdminController
    Route::get('/produits', [ProduitController::class, 'index']);
    Route::post('/produits', [ProduitController::class, 'store']);
    Route::put('/produits/{id}', [ProduitController::class, 'update']);
    Route::delete('/produits/{id}', [ProduitController::class, 'destroy']);
});
