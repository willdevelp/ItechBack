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
            'image' => 'required|string|',
            'image_public_id' => 'required|string',
        ]);

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
            'image' => 'required|string|',
        ]);

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

        $publicity->delete();

        return response()->json([
            'message' => 'Publicity deleted successfully'
        ], 200);
    }
}