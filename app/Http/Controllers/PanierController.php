<?php

namespace App\Http\Controllers;

use App\Models\Panier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $validated = $request->validate([
            'product_name' => 'required|string|exists:products,name',
            'quantity' => 'required|integer|min:1|max:100'
        ]);
    
        // Récupération du produit
        $product = Product::where('name', $validated['product_name'])->firstOrFail();
        $user = auth()->user();
    
        // Utilisation d'une transaction pour plus de sécurité
        return DB::transaction(function () use ($user, $product, $validated) {
            $panier = Panier::firstOrCreate(['user_id' => $user->id]);
    
            // Vérification existante avec le bon product_id
            $existingProduct = $panier->products()
                ->where('product_id', $product->id)
                ->first();
    
            if ($existingProduct) {
                // Mise à jour de la quantité
                $newQuantity = $existingProduct->pivot->quantity + $validated['quantity'];
                $panier->products()->updateExistingPivot($product->id, [
                    'quantity' => $newQuantity
                ]);
            } else {
                // Ajout du nouveau produit
                $panier->products()->attach($product->id, [
                    'quantity' => $validated['quantity']
                ]);
            }
    
            // Retourne les données fraîches du panier
            $panier->load('products');
            return response()->json([
                'message' => 'Produit ajouté au panier',
                'panier' => $panier
            ]);
        });
    }

    public function updateProduit(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $panier = Panier::firstOrCreate(['user_id' => $user->id]);

        $panier->products()->updateExistingPivot($request->product_id, [
            'quantity' => $request->quantity
        ]);

        return response()->json(['message' => 'Quantité mise à jour']);
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
