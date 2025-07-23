<?php

namespace App\Http\Controllers;

use App\Models\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class CommandController extends Controller
{
        public function index()
    {
        $commands = Command::with('user:id,name')->get();
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

    /**
     * Affiche les produits les plus vendus avec le nombre de ventes.
     */
    public function bestSellers()
    {
        $products = DB::table('commande_produit')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(10)
            ->get();

        // Charger les infos produit
        $result = [];
        foreach ($products as $row) {
            $product = Product::find($row->product_id);
            if ($product) {
                $result[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'promoprice' => $product->promoprice,
                    'image' => $product->image,
                    'total_sold' => $row->total_sold,
                ];
            }
        }

        return response()->json($result, 200);
    }
}
