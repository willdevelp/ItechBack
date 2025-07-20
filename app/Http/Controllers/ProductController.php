<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category'])
            ->select('id', 'name', 'description', 'price', 'promoprice', 'image', 'category_id')
            ->get();
        return response()->json($products);
    }

        /**
     * Affiche la liste des 10 produits dont le promoprice est différent de 0.00, toutes catégories confondues.
     */
    public function topPromoProducts()
    {
        $products = Product::where('promoprice', '!=', 0.00)
            ->orderByDesc('promoprice')
            ->take(12)
            ->get();

        return response()->json($products, 200);
    }

     /**
     * Affiche la liste des 10 derniers produits ajoutés.
     */
    public function latestProducts()
    {
        $products = Product::with(['category'])
            ->orderByDesc('created_at')
            ->take(12)
            ->get();

        return response()->json($products, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'promoprice' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = $imagePath;
        }

        $product = Product::create($data);

        return response()->json(['message' => 'Product created successfully', 'product' => $product, 'image_url' => $product->image ? asset("storage/{$product->image}") : null], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($name)
    {
        // Charge le produit avec ses relations
        $product = Product::with('category')->findOrFail($name);

        // Structure de la réponse
        $response = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'image' => $product->image,
            'promoprice' => $product->promoprice,
            'category' => [
                'id' => $product->category->id,
                'name' => $product->category->name
            ],
        ];
        return response()->json($response, 200);
    }

    public function showByCategory($categoryName)
    {
        $products = Product::where('category_name', $categoryName)->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found for this category'], 404);
        }

        return response()->json($products, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'promoprice' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        $product = Product::findOrFail($id);

        // Gestion de l'image
        if (request()->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($product->image) {
                \Storage::disk('public')->delete($product->image);
            }
            $imagePath = request()->file('image')->store('products', 'public');
            $data['image'] = $imagePath;
        } else {
            // Si aucune nouvelle image n'est fournie, conserver l'ancienne
            $data['image'] = $product->image;
        }

        // Mettre à jour le produit
        $product->update($data);

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Supprimer l'image du produit si elle existe
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
