<?php

namespace App\Http\Controllers;

use App\Models\Panier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PanierController extends Controller
{
    public function getPanier()
    {
        $user = auth()->user();
        $panier = Panier::firstOrCreate(['user_id' => $user->id]);
        $panier->load('products');

        return response()->json($panier);
    }

    public function addProduit(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $panier = Panier::firstOrCreate(['user_id' => $user->id]);

        // Vérifie si le produit existe déjà dans le panier
        $exists = $panier->products()->where('product_id', $request->product_id)->exists();

        if ($exists) {
            // Incrémente la quantité
            $currentQuantity = $panier->products()->where('product_id', $request->product_id)->first()->pivot->quantity;
            $panier->products()->updateExistingPivot($request->product_id, [
                'quantity' => $currentQuantity + $request->quantity
            ]);
        } else {
            $panier->products()->attach($request->product_id, ['quantity' => $request->quantity]);
        }

        return response()->json(['message' => 'Produit ajouté au panier']);
    }

    public function removeProduit(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $user = auth()->user();
        $panier = Panier::where('user_id', $user->id)->first();

        if ($panier) {
            $panier->products()->detach($request->product_id);
        }

        return response()->json(['message' => 'Produit retiré du panier']);
    }

    public function countProduits()
    {
        $user = Auth::user();
        $panier = Panier::where('user_id', $user->id)->first();

        $count = 0;
        if ($panier) {
            $count = $panier->products()->sum('panier_produit.quantity');
        }

        return response()->json(['count' => $count]);
    }
}
