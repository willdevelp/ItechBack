<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories, 200);
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
            'name' => 'required|string|max:255|unique:categories,name',
        ]);
    
        // Gestion de l'image avec Cloudinary
        if ($request->hasFile('image')) {
            // Upload vers Cloudinary
            $uploadedFile = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'categories', // Dossier dans Cloudinary
                'public_id' => 'category_'.time(), // Nom unique pour le fichier
                'transformation' => [
                    'width' => 800,
                    'height' => 800,
                    'crop' => 'limit'
                ]
            ]);
    
            // Stocke l'URL sécurisée et le public_id
            $data['image'] = $uploadedFile->getSecurePath();
            $data['image_public_id'] = $uploadedFile->getPublicId();
        }
    
        // Crée la catégorie avec les données validées
        $data['image'] = $data['image'] ?? null;
        $data['image_public_id'] = $data['image_public_id'] ?? null;
        $data['name'] = trim($data['name']);
    
        $category = Category::create($data);
    
        return response()->json([
            'message' => 'Category created successfully', 
            'category' => $category,
            'image_url' => $data['image']
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        // Gestion de l'image avec Cloudinary
        if ($request->hasFile('image')) {
            // Upload vers Cloudinary
            $uploadedFile = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'categories', // Dossier dans Cloudinary
                'public_id' => 'category_'.time(), // Nom unique pour le fichier
                'transformation' => [
                    'width' => 800,
                    'height' => 800,
                    'crop' => 'limit'
                ]
            ]);
    
            // Stocke l'URL sécurisée et le public_id
            $data['image'] = $uploadedFile->getSecurePath();
            $data['image_public_id'] = $uploadedFile->getPublicId();
        }

        // Met à jour la catégorie avec les données validées
        $category->update($data);

        return response()->json([
            'message' => 'Category updated successfully', 
            'category' => $category,
            'image_url' => $data['image']
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
}
