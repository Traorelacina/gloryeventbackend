<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProduitController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(): JsonResponse
    {
        try {
            // Pour l'admin, on veut tous les produits, pas seulement ceux en stock
            $produits = Produit::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $produits
            ]);
        } catch (\Exception $e) {
            \Log::error('Products index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            \Log::info('Store method called', ['request_data' => $request->all()]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'category' => 'required|string|max:255',
                'in_stock' => 'sometimes|boolean',
                'featured' => 'sometimes|boolean',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            \Log::info('Validation passed', $validated);

            // Générer un slug unique
            $slug = Str::slug($validated['name']);
            $count = Produit::where('slug', $slug)->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 1);
            }

            // Gérer l'upload de l'image
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('produits', 'public');
                \Log::info('Image uploaded to: ' . $imagePath);
            }

            $produit = Produit::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'],
                'price' => $validated['price'],
                'category' => $validated['category'],
                'in_stock' => $validated['in_stock'] ?? true,
                'featured' => $validated['featured'] ?? false,
                'image' => $imagePath
            ]);

            \Log::info('Product created successfully', ['id' => $produit->id]);

            return response()->json([
                'success' => true,
                'message' => 'Produit créé avec succès',
                'data' => $produit
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Product creation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du produit: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $produit = Produit::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
                'category' => 'sometimes|string|max:255',
                'in_stock' => 'boolean',
                'featured' => 'boolean',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            // Gérer l'upload de la nouvelle image si fournie
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image si elle existe
                if ($produit->image && Storage::disk('public')->exists($produit->image)) {
                    Storage::disk('public')->delete($produit->image);
                }
                
                $imagePath = $request->file('image')->store('produits', 'public');
                $validated['image'] = $imagePath;
            }

            $produit->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Produit mis à jour avec succès',
                'data' => $produit
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Product update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du produit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $produit = Produit::findOrFail($id);

            // Supprimer l'image associée
            if ($produit->image && Storage::disk('public')->exists($produit->image)) {
                Storage::disk('public')->delete($produit->image);
            }

            $produit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Product deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du produit',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}