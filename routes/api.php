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

// Statistiques
Route::post('/track-view', [PageViewController::class, 'trackView']);
Route::get('/statistics', [PageViewController::class, 'getStatistics'])->middleware('auth:sanctum');
Route::get('/dashboard-stats', [PageViewController::class, 'getDashboardStats'])->middleware('auth:sanctum');

// Produits publics
Route::get('/produits', [ProduitController::class, 'index']);
Route::get('/produits/featured', [ProduitController::class, 'featured']);
Route::get('/produits/category/{category}', [ProduitController::class, 'byCategory']);
Route::get('/produits/{slug}', [ProduitController::class, 'show']);

// PORTFOLIO - Routes publiques (lecture seule)
Route::get('/portfolio', [PortfolioController::class, 'index']);
Route::get('/portfolio/featured', [PortfolioController::class, 'featured']);
Route::get('/portfolio/category/{category}', [PortfolioController::class, 'byCategory']);
Route::get('/portfolio/{id}', [PortfolioController::class, 'show']);
Route::get('/portfolio/category/{category}', [PortfolioController::class, 'getByCategory']);
// Contacts
Route::post('/contacts', [ContactController::class, 'store']);

// Commandes
Route::post('/commandes', [CommandeController::class, 'store']);
Route::get('/commandes/{id}', [CommandeController::class, 'show']);

// Protected admin routes
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/recent-orders', [AdminController::class, 'recentOrders']);
    Route::get('/recent-contacts', [AdminController::class, 'recentContacts']);
    
    // Contacts management
    Route::get('/contacts', [AdminController::class, 'recentContacts']);
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);
    Route::put('/contacts/{id}/read', [ContactController::class, 'markAsRead']);
    
    // Commandes management
    Route::get('/commandes', [AdminController::class, 'commandes']);
    Route::get('/commandes/{id}', [AdminController::class, 'showCommande']);
    Route::put('/commandes/{id}', [AdminController::class, 'updateCommande']);
    Route::delete('/commandes/{id}', [AdminController::class, 'destroyCommande']);
    
    // Product management
    Route::get('/produits', [ProduitController::class, 'index']);
    Route::post('/produits', [ProduitController::class, 'store']);
    Route::put('/produits/{id}', [ProduitController::class, 'update']);
    Route::delete('/produits/{id}', [ProduitController::class, 'destroy']);
    
    // PORTFOLIO - Routes d'administration (CRUD complet)
    Route::post('/portfolio', [PortfolioController::class, 'store']); // POST /admin/portfolio
    Route::put('/portfolio/{id}', [PortfolioController::class, 'update']); // PUT /admin/portfolio/{id}
    Route::delete('/portfolio/{id}', [PortfolioController::class, 'destroy']); // DELETE /admin/portfolio/{id}
});
