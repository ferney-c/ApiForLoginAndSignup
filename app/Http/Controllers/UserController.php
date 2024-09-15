<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|min:10|max:10',
            'password' => 'required|min:8',
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $query = User::where(function ($query) use ($request) {
            return $query->where('email', '=', $request->email)
                ->orWhere('phone', '=', $request->phone);
        })->first();

        if($query) {
            return response()->json([
                'success' => 0,
                'message' => 'El correo electrónico o el teléfono ya están registrados',
            ], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);
        
        $token = $user->createToken($request->email)->plainTextToken;

        return response()->json([
            'success' => 1,
            'token' => $token,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // mostrar la información del usuario
            $user = User::find($id);
            return response()->json([
                'success' => 1,
                'datum' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Login a user.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|string',
            'password' => 'required|string',
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials)) {
            return response()->json([
                'success' => 0,
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        $user = $request->user();

        // Eliminar tokens anteriores del usuario
        $user->tokens()->delete();

        $token = $user->createToken($request->email)->plainTextToken;

        return response()->json([
            'success' => 1,
            'token' => $token,
        ], 200);
    }
}
