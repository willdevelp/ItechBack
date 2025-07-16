<?php

namespace App\Http\Controllers;

use App\Models\Publicity;
use Illuminate\Http\Request;

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
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            // 'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Gestion de l'image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('publicities', 'public');
            $data['image'] = $imagePath;
        }

        $publicity = Publicity::create($data);

        return response()->json(['message' => 'Publicity created successfully', 'publicity' => $publicity, 'image_url' => asset("storage/{$publicity->image}")], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Publicity $publicity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Publicity $publicity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Publicity $publicity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Publicity $publicity)
    {
        $publicity->delete();

        return response()->json(['message' => 'Publicity deleted successfully'], 200);
    }
}
