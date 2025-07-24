<?php

namespace App\Http\Controllers;

use App\Models\Publicity;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PublicityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $publicities = Publicity::all();
        return response()->json($publicities, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Gestion de l'image avec Cloudinary
        if ($request->hasFile('image')) {
            try {
                $uploadedFile = Cloudinary::upload($request->file('image')->getRealPath(), [
                    'folder' => 'publicities',
                    'transformation' => [
                        'width' => 1200,
                        'height' => 630,
                        'crop' => 'fill',
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

        $publicity = Publicity::create($data);

        return response()->json([
            'message' => 'Publicity created successfully',
            'publicity' => $publicity
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $publicity = Publicity::findOrFail($id);
        
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Gestion de l'image avec Cloudinary
        if ($request->hasFile('image')) {
            try {
                // Supprimer l'ancienne image si elle existe
                if ($publicity->image_public_id) {
                    Cloudinary::destroy($publicity->image_public_id);
                }

                $uploadedFile = Cloudinary::upload($request->file('image')->getRealPath(), [
                    'folder' => 'publicities',
                    'transformation' => [
                        'width' => 1200,
                        'height' => 630,
                        'crop' => 'fill',
                        'quality' => 'auto'
                    ]
                ]);

                $data['image'] = $uploadedFile->getSecurePath();
                $data['image_public_id'] = $uploadedFile->getPublicId();
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image update failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $publicity->update($data);

        return response()->json([
            'message' => 'Publicity updated successfully',
            'publicity' => $publicity
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $publicity = Publicity::findOrFail($id);

        // Supprimer l'image de Cloudinary si elle existe
        if ($publicity->image_public_id) {
            Cloudinary::destroy($publicity->image_public_id);
        }

        $publicity->delete();

        return response()->json([
            'message' => 'Publicity deleted successfully'
        ], 200);
    }
}