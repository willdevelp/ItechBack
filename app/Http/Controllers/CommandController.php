<?php

namespace App\Http\Controllers;

use App\Models\Command;
use Illuminate\Http\Request;

class CommandController extends Controller
{
    public function index()
    {
        $commands = Command::all();
        return response()->json($commands, 200);
    }

    public function passerCommande(Request $request)
    {
        $request->validate([
            'payment' => 'required|string',
            'datecom' => 'nullable|date', // Date de la commande, optionnelle
            'total_price' => 'required|numeric',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();

        $commande = Command::create([
            'user_id' => $user->id,
            'payment' => $request->payment,
            'datecom' => $request->datecom ?? now(),
            'total_price' => $request->total_price,
            'status' => 'en_attente',
        ]);

        foreach ($request->products as $product) {
            $commande->products()->attach($product['id'], ['quantity' => $product['quantity']]);
        }

        // Vider le panier
        // $user->panier->products()->detach();

        return response()->json(['message' => 'Commande passée avec succès']);
    }

    public function mesCommandes()
    {
        $user = auth()->user();
        $commandes = Command::with('products')->where('user_id', $user->id)->get();

        return response()->json($commandes);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:en_attente,en cours,livrée,annulée',
        ]);

        $commande = Command::findOrFail($id);
        $commande->status = $request->status;
        $commande->save();

        return response()->json(['message' => 'Statut de la commande mis à jour avec succès']);
    }

    public function delete(){
        $user = auth()->user();
        $commandes = Command::where('user_id', $user->id)->get();

        if ($commandes->isEmpty()) {
            return response()->json(['message' => 'Aucune commande à supprimer'], 404);
        }

        foreach ($commandes as $commande) {
            $commande->delete();
        }

        return response()->json(['message' => 'Toutes les commandes ont été supprimées avec succès']);
    }
}
