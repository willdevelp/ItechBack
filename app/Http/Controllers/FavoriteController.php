<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Models\User;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($userId)
{
    // Vérifier d'abord si l'utilisateur existe
    $user = User::find($userId);

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Utilisateur non trouvé'
        ], 404);
    }

    // Récupérer les favoris de l'utilisateur avec les relations
    $favorites = Favorite::with(['product' => function($query) {
            $query->select('id', 'name', 'price', 'image'); // Champs nécessaires du produit
        }])
        ->where('user_id', $userId)
        ->select('id', 'product_id', 'created_at')
        ->orderBy('created_at', 'desc') // Optionnel: tri par date
        ->get();

    return response()->json([
        'status' => true,
        'user_id' => $userId,
        'favorites' => $favorites,
        'count' => $favorites->count()
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ]);

        // Check if the favorite already exists
        $existingFavorite = Favorite::where('user_id', $data['user_id'])
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existingFavorite) {
            return response()->json(['message' => 'This product is already in your favorites'], 409);
        }

        // Create the favorite
        $favorite = Favorite::create($data);

        return response()->json(['message' => 'Product added to favorites successfully', 'favorite' => $favorite], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($userId)
{
    // Vérifie d'abord si l'utilisateur existe
    $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Récupère tous les favoris de l'utilisateur avec les produits associés
    $favorites = Favorite::with(['product' => function($query) {
            $query->select('id', 'name', 'price', 'image'); // Champs spécifiques du produit
        }])
        ->where('user_id', $userId)
        ->get(['id', 'product_id', 'created_at']); // Champs spécifiques du favori

    if ($favorites->isEmpty()) {
        return response()->json(['message' => 'No favorites found for this user'], 200);
    }

    return response()->json([
        'user_id' => $userId,
        'favorites' => $favorites
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Favorite $favorite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Favorite $favorite)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $favorite = Favorite::find($id);

        if (!$favorite) {
            return response()->json(['message' => 'Favorite not found'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Favorite removed successfully'], 200);
    }
}
