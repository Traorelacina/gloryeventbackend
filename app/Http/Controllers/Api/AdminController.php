<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Contact;
use App\Models\Produit;
use App\Models\Service;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function dashboard(): JsonResponse
    {
        try {
            $stats = [
                'total_services' => Service::count(),
                'total_produits' => Produit::count(),
                'total_commandes' => Commande::count(),
                'commandes_en_attente' => Commande::where('status', 'en_attente')->count(),
                'total_contacts' => Contact::count(),
                'total_portfolio' => Portfolio::count(),
                'revenue_total' => Commande::where('status', '!=', 'annulee')->sum('total'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Get all products
     */
    public function produits(): JsonResponse
    {
        try {
            $produits = Produit::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $produits
            ]);
        } catch (\Exception $e) {
            \Log::error('Produits list error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits'
            ], 500);
        }
    }

    /**
     * Store a new product
     */
    public function storeProduit(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:produits,slug',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|string|max:255',
                'category' => 'required|string|max:100',
                'in_stock' => 'nullable|boolean',
                'featured' => 'nullable|boolean',
            ]);

            $produit = Produit::create([
                'name' => $request->name,
                'slug' => $request->slug ?? \Str::slug($request->name),
                'description' => $request->description,
                'price' => $request->price,
                'image' => $request->image ?? '',
                'category' => $request->category,
                'in_stock' => $request->in_stock ?? true,
                'featured' => $request->featured ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Produit créé avec succès',
                'data' => $produit
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Create product error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du produit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a product
     */
    public function updateProduit(Request $request, $id): JsonResponse
    {
        try {
            $produit = Produit::findOrFail($id);

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'slug' => 'sometimes|string|max:255|unique:produits,slug,' . $id,
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
                'image' => 'nullable|string|max:255',
                'category' => 'sometimes|string|max:100',
                'in_stock' => 'sometimes|boolean',
                'featured' => 'sometimes|boolean',
            ]);

            $produit->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Produit mis à jour avec succès',
                'data' => $produit
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Update product error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du produit'
            ], 500);
        }
    }

    /**
     * Delete a product
     */
    public function destroyProduit($id): JsonResponse
    {
        try {
            $produit = Produit::findOrFail($id);
            $produit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error('Delete product error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du produit'
            ], 500);
        }
    }

    /**
     * Get all commandes
     */
    public function commandes(): JsonResponse
    {
        try {
            $commandes = Commande::with('produits')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $commandes
            ]);
        } catch (\Exception $e) {
            \Log::error('Commandes list error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes',
            ], 500);
        }
    }

    /**
     * Get single commande with products details
     */
    public function showCommande($id): JsonResponse
    {
        try {
            \Log::info('Fetching commande details', ['id' => $id]);

            // Récupérer la commande avec ses produits
            $commande = Commande::with(['produits' => function($query) {
                $query->select('produits.id', 'produits.name', 'produits.slug', 'produits.image', 'produits.price');
            }])->findOrFail($id);

            // Formater les produits pour inclure toutes les informations nécessaires
            $commandeData = $commande->toArray();
            
            // S'assurer que les produits ont toutes les bonnes données
            if (isset($commandeData['produits'])) {
                $commandeData['produits'] = collect($commandeData['produits'])->map(function($produit) {
                    return [
                        'id' => $produit['id'],
                        'name' => $produit['name'],
                        'slug' => $produit['slug'] ?? '',
                        'image' => $produit['image'],
                        'price' => floatval($produit['pivot']['price'] ?? $produit['price']),
                        'quantity' => intval($produit['pivot']['quantity'] ?? 1),
                    ];
                })->toArray();
            }

            \Log::info('Commande details fetched successfully', ['id' => $id]);

            return response()->json([
                'success' => true,
                'data' => $commandeData
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Commande not found: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Show commande error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de la commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update commande status
     */
    public function updateCommande(Request $request, $id): JsonResponse
    {
        try {
            \Log::info('Updating commande status', ['id' => $id, 'data' => $request->all()]);

            $commande = Commande::findOrFail($id);

            $validated = $request->validate([
                'status' => 'required|in:en_attente,en_cours,livree,annulee'
            ]);

            $commande->update($validated);

            \Log::info('Commande status updated successfully', ['id' => $id, 'new_status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Statut de la commande mis à jour avec succès',
                'data' => $commande
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Update commande error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete commande
     */
    public function destroyCommande($id): JsonResponse
    {
        try {
            $commande = Commande::findOrFail($id);
            $commande->delete();

            return response()->json([
                'success' => true,
                'message' => 'Commande supprimée avec succès'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Delete commande error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la commande'
            ], 500);
        }
    }

    /**
     * Get recent orders.
     */
    public function recentOrders(): JsonResponse
    {
        try {
            $commandes = Commande::with('produits')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $commandes
            ]);
        } catch (\Exception $e) {
            \Log::error('Recent orders error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes récentes'
            ], 500);
        }
    }

    /**
     * Get recent contacts.
     */
    public function recentContacts(): JsonResponse
    {
        try {
            $contacts = Contact::orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $contacts
            ]);
        } catch (\Exception $e) {
            \Log::error('Recent contacts error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des contacts récents'
            ], 500);
        }
    }
}