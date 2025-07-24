<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category'])
            ->select('id', 'name', 'description', 'price', 'promoprice', 'image', 'category_id', 'image_public_id')
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        // Gestion de l'image avec Cloudinary
        if ($request->hasFile('image')) {
            try {
                $uploadedFile = Cloudinary::upload($request->file('image')->getRealPath(), [
                    'folder' => 'products',
                    'transformation' => [
                        'width' => 800,
                        'height' => 800,
                        'crop' => 'limit',
                        'quality' => 'auto'
                    ]
                ]);

                $data['image'] = $uploadedFile->getSecurePath();
                $data['image_public_id'] = $uploadedFile->getPublicId();
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image upload failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $product = Product::create($data);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($name)
    {
        $product = Product::with('category')
                ->where('name', $name)
                ->firstOrFail();

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
        $products = Product::whereHas('category', function ($query) use ($categoryName) {
            $query->where('name', $categoryName);
        })->get();
    
        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found for this category'], 404);
        }
    
        return response()->json($products, 200);
    }

    /**
     * Recherche des produits
     */
    public function search(Request $request)
    {
        $query = $request->query('q');
        
        if (empty($query)) {
            return response()->json([], 200);
        }

        $products = Product::with(['category'])
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhereHas('category', function($categoryQuery) use ($query) {
                      $categoryQuery->where('name', 'like', "%{$query}%");
                  });
            })
            ->select('id', 'name', 'description', 'price', 'promoprice', 'image', 'category_id')
            ->limit(10)
            ->get()
            ->map(function($product) {
                $product->category_name = $product->category->name;
                return $product;
            });

        return response()->json($products);
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $product = Product::findOrFail($id);

        // Gestion de l'image avec Cloudinary
        if (request()->hasFile('image')) {
            try {
                // Supprimer l'ancienne image de Cloudinary si elle existe
                if ($product->image_public_id) {
                    Cloudinary::destroy($product->image_public_id);
                }

                $uploadedFile = Cloudinary::upload(request()->file('image')->getRealPath(), [
                    'folder' => 'products',
                    'transformation' => [
                        'width' => 800,
                        'height' => 800,
                        'crop' => 'limit',
                        'quality' => 'auto'
                    ]
                ]);

                $data['image'] = $uploadedFile->getSecurePath();
                $data['image_public_id'] = $uploadedFile->getPublicId();
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image upload failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            // Conserver les valeurs existantes si aucune nouvelle image n'est fournie
            $data['image'] = $product->image;
            $data['image_public_id'] = $product->image_public_id;
        }

        $product->update($data);

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Supprimer l'image de Cloudinary si elle existe
        if ($product->image_public_id) {
            Cloudinary::destroy($product->image_public_id);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}