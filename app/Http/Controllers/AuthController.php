<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:15|unique:users,phone_number',
                'password' => 'required|string',
                'fcm_token' => 'nullable|string',
                'company_name' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'address_link' => 'nullable|string',
                'purchase_date' => 'nullable|date',
                'expiry_date' => 'nullable|date',
                'model_number' => 'nullable|string|max:255', // new
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
                'fcm_token' => $request->fcm_token,
                'company_name' => $request->company_name,
                'address' => $request->address,
                'address_link' => $request->address_link,
                'purchase_date' => $request->purchase_date,
                'expiry_date' => $request->expiry_date,
                'model_number' => $request->model_number, // new
            ]);

            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json(['token' => $token, 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|string',
                'password' => 'required|string',
                'fcm_token' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = User::where('phone_number', $request->phone_number)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $user->update(['last_login_at' => now(), 'fcm_token' => $request->fcm_token]);
            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json(['token' => $token, 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Login failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Logout failed', 'message' => $e->getMessage()], 500);
        }
    }
}
