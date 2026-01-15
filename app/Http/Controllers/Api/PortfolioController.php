<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioImage; // CET IMPORT EST ESSENTIEL
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the portfolio items.
     */
    public function index(): JsonResponse
    {
        try {
            $portfolios = Portfolio::with('images')->orderBy('date', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $portfolios
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching portfolios:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du portfolio'
            ], 500);
        }
    }

    /**
 * Get portfolios by category
 */
public function getByCategory($category): JsonResponse
{
    try {
        $portfolios = Portfolio::with('images')
            ->where('category', $category)
            ->orderBy('date', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $portfolios
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching portfolios by category:', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération du portfolio'
        ], 500);
    }
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('Store portfolio request received', ['request_data' => $request->except(['image', 'additional_images'])]);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:mariage,corporate,anniversaire,evenement_professionnel',
            'featured' => 'boolean',
            'date' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed in store:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Gérer l'upload de l'image principale
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('portfolios', 'public');
                $imagePath = 'storage/' . $path;
                Log::info('Main image stored:', ['path' => $imagePath]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'image principale est requise'
                ], 422);
            }

            $portfolio = Portfolio::create([
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'featured' => $request->boolean('featured'),
                'date' => $request->date,
                'image' => $imagePath,
            ]);

            Log::info('Portfolio created:', ['id' => $portfolio->id]);

            // Gérer les images supplémentaires
            if ($request->hasFile('additional_images')) {
                $additionalImages = $request->file('additional_images');
                Log::info('Additional images received:', ['count' => count($additionalImages)]);
                
                foreach ($additionalImages as $index => $file) {
                    if ($file && $file->isValid()) {
                        $additionalPath = $file->store('portfolios/additional', 'public');
                        
                        // Utilisez PortfolioImage directement
                        PortfolioImage::create([
                            'portfolio_id' => $portfolio->id,
                            'image_path' => 'storage/' . $additionalPath,
                            'order' => $index,
                        ]);
                        
                        Log::info('Additional image stored:', ['index' => $index, 'path' => $additionalPath]);
                    }
                }
            }

            // Charger les images supplémentaires avec la réponse
            $portfolio->load('images');

            return response()->json([
                'success' => true,
                'message' => 'Portfolio créé avec succès',
                'data' => $portfolio
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating portfolio:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du portfolio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified portfolio item.
     */
    
    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        Log::info('Update portfolio request received', [
            'portfolio_id' => $id,
            'request_data' => $request->except(['image', 'additional_images', 'deleted_images'])
        ]);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:mariage,corporate,anniversaire,evenement_professionnel',
            'featured' => 'boolean',
            'date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'integer',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed in update:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $portfolio = Portfolio::with('images')->findOrFail($id);

            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'featured' => $request->boolean('featured'),
                'date' => $request->date,
            ];

            // Gérer l'upload de la nouvelle image principale
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                // Supprimer l'ancienne image si elle existe
                if ($portfolio->image) {
                    $oldImage = str_replace('storage/', '', $portfolio->image);
                    if (Storage::disk('public')->exists($oldImage)) {
                        Storage::disk('public')->delete($oldImage);
                        Log::info('Old main image deleted:', ['path' => $oldImage]);
                    }
                }
                
                $path = $request->file('image')->store('portfolios', 'public');
                $data['image'] = 'storage/' . $path;
                Log::info('New main image stored:', ['path' => $data['image']]);
            }

            $portfolio->update($data);
            Log::info('Portfolio updated:', ['id' => $portfolio->id]);

            // Gérer les images supplémentaires à supprimer
            if ($request->has('deleted_images')) {
                foreach ($request->deleted_images as $imageId) {
                    $image = PortfolioImage::find($imageId);
                    if ($image && $image->portfolio_id == $portfolio->id) {
                        // Supprimer le fichier
                        $imagePath = str_replace('storage/', '', $image->image_path);
                        if (Storage::disk('public')->exists($imagePath)) {
                            Storage::disk('public')->delete($imagePath);
                        }
                        // Supprimer l'enregistrement
                        $image->delete();
                    }
                }
            }

            // Gérer les nouvelles images supplémentaires
            if ($request->hasFile('additional_images')) {
                $existingImagesCount = $portfolio->images()->count();
                
                foreach ($request->file('additional_images') as $index => $file) {
                    $additionalPath = $file->store('portfolios/additional', 'public');
                    
                    PortfolioImage::create([
                        'portfolio_id' => $portfolio->id,
                        'image_path' => 'storage/' . $additionalPath,
                        'order' => $existingImagesCount + $index,
                    ]);
                }
            }

            // Recharger les images
            $portfolio->load('images');

            return response()->json([
                'success' => true,
                'message' => 'Portfolio mis à jour avec succès',
                'data' => $portfolio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du portfolio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $portfolio = Portfolio::findOrFail($id);
            
            // Supprimer l'image principale
            if ($portfolio->image) {
                $imagePath = str_replace('storage/', '', $portfolio->image);
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
            
            // Supprimer les images supplémentaires
            foreach ($portfolio->images as $image) {
                $additionalPath = str_replace('storage/', '', $image->image_path);
                if (Storage::disk('public')->exists($additionalPath)) {
                    Storage::disk('public')->delete($additionalPath);
                }
                $image->delete();
            }
            
            $portfolio->delete();

            return response()->json([
                'success' => true,
                'message' => 'Portfolio supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du portfolio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified portfolio item.
     */
    public function show($id): JsonResponse
    {
        try {
            $portfolio = Portfolio::with('images')->findOrFail($id);
            
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
}
