<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    /**
     * Récupère tous les utilisateurs
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::select(['id', 'name', 'surname', 'email', 'phone', 'role', 'created_at'])
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        return response()->json($users);
    }

    /**
     * Récupère un utilisateur spécifique
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::select(['id', 'name', 'surname', 'email', 'phone', 'role', 'address', 'postal_code', 'country', 'created_at'])
                   ->findOrFail($id);
        
        return response()->json($user);
    }
}
