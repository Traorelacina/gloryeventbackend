<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts for admin.
     */
    public function index(): JsonResponse
    {
        try {
            $contacts = Contact::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $contacts
            ]);

        } catch (\Exception $e) {
            \Log::error('Fetch contacts error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des contacts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created contact message.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:50',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'service' => 'nullable|string|max:100'
            ]);

            $contact = Contact::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Message envoyé avec succès',
                'data' => $contact
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Contact form error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified contact.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $contact = Contact::findOrFail($id);
            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Delete contact error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer un contact comme lu/non lu
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'is_read' => 'required|boolean'
            ]);

            $contact = Contact::findOrFail($id);
            $contact->update([
                'is_read' => $validated['is_read']
            ]);

            return response()->json([
                'success' => true,
                'message' => $validated['is_read'] ? 'Message marqué comme lu' : 'Message marqué comme non lu',
                'data' => $contact
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Mark as read error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}