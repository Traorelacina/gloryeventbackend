<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CommandeController extends Controller
{
    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            \Log::info('Creating order', ['request_data' => $request->all()]);

            $validated = $request->validate([
                'client_name' => 'required|string|max:255',
                'client_email' => 'required|email',
                'client_phone' => 'required|string|max:50',
                'produits' => 'required|array|min:1',
                'produits.*.id' => 'required|exists:produits,id',
                'produits.*.quantity' => 'required|integer|min:1'
            ]);

            // Calculer le total et préparer les données des produits
            $total = 0;
            $produitsData = [];

            foreach ($validated['produits'] as $item) {
                $produit = Produit::findOrFail($item['id']);
                
                // Vérifier le stock
                if (!$produit->in_stock) {
                    throw new \Exception("Le produit '{$produit->name}' n'est plus en stock");
                }

                $quantity = $item['quantity'];
                $price = $produit->price;
                $subtotal = $price * $quantity;
                $total += $subtotal;

                // Stocker les données pour l'attachement
                $produitsData[$produit->id] = [
                    'quantity' => $quantity,
                    'price' => $price, // Sauvegarder le prix au moment de la commande
                ];
            }

            // Créer la commande
            $commande = Commande::create([
                'client_name' => $validated['client_name'],
                'client_email' => $validated['client_email'],
                'client_phone' => $validated['client_phone'],
                'total' => $total,
                'status' => 'en_attente'
            ]);

            // Attacher les produits avec quantity et price
            $commande->produits()->attach($produitsData);

            // Charger les produits pour la réponse
            $commande->load('produits');

            DB::commit();

            \Log::info('Order created successfully', ['commande_id' => $commande->id]);

            // Envoi d'email de confirmation (optionnel)
            try {
                $this->sendConfirmationEmail($commande);
            } catch (\Exception $e) {
                \Log::warning('Failed to send confirmation email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès',
                'data' => $commande
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order creation error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show($id): JsonResponse
    {
        try {
            $commande = Commande::with(['produits' => function($query) {
                $query->select('produits.id', 'produits.name', 'produits.slug', 'produits.image', 'produits.price');
            }])->findOrFail($id);

            // Formater les produits avec les bonnes données du pivot
            $commandeData = $commande->toArray();
            
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
            
            return response()->json([
                'success' => true,
                'data' => $commandeData
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Show commande error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send confirmation email
     */
    private function sendConfirmationEmail(Commande $commande): void
    {
        // TODO: Implémenter l'envoi d'email
        // Exemple avec Laravel Mail:
        /*
        Mail::to($commande->client_email)->send(
            new \App\Mail\CommandeConfirmation($commande)
        );
        */
        
        \Log::info('Email confirmation would be sent to: ' . $commande->client_email);
    }
}