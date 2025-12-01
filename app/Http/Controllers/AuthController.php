<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Validation des données avec device_name optionnel
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'device_name' => 'sometimes|string', // Rendre device_name optionnel
            ]);

            // Recherche de l'admin
            $admin = Admin::where('email', $request->email)->first();

            // Vérification du mot de passe
            if (!$admin || !Hash::check($request->password, $admin->password)) {
                return response()->json([
                    'message' => 'Les identifiants fournis sont incorrects.'
                ], 401);
            }

            // Suppression des anciens tokens
            $deviceName = $request->device_name ?? 'web-admin';
            $admin->tokens()->where('name', $deviceName)->delete();

            // Création du nouveau token
            $token = $admin->createToken($deviceName)->plainTextToken;

            return response()->json([
                'user' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role' => $admin->role,
                    'role_label' => Admin::$roles[$admin->role] ?? $admin->role,
                ],
                'token' => $token,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la connexion: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'message' => 'Déconnexion réussie'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }
}