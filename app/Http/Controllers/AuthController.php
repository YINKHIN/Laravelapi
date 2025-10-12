<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function index()
    {
        return response()->json([
            "list" => User::with('profile')->get()
        ]);
    }

    // Login a user and return a Sanctum token
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load the profile relationship
        $user->load('profile');

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'type' => 'sometimes|in:admin,user',
            'phone' => 'sometimes|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'address' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type ?? 'user',
        ]);

        // Create profile if additional data provided
        $profileData = [];
        
        if ($request->has(['phone', 'address']) || $request->hasFile('image')) {
            if ($request->phone) $profileData['phone'] = $request->phone;
            if ($request->address) $profileData['address'] = $request->address;
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('profiles', 'r2');
                $profileData['image'] = $imagePath;
            }
            
            if (!empty($profileData)) {
                $profileData['user_id'] = $user->id;
                Profile::create($profileData);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Load the profile relationship
        $user->load('profile');

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('profile');

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request, string $id)
    {
        // Authorization check: Only allow update own profile
        if (Auth::id() != $id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Can only update own profile'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'sometimes|nullable|min:6',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'type' => 'nullable|in:admin,user',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($id);
        
        // Update user basic info
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];
        
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        
        if ($request->filled('type')) {
            $userData['type'] = $request->type;
        }
        
        $user->update($userData);
        
        // Update or create profile
        $profile = Profile::firstOrCreate(['user_id' => $user->id]);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($profile->image && Storage::disk('r2')->exists($profile->image)) {
                Storage::disk('r2')->delete($profile->image);
            }
            
            // Store new image
            $imagePath = $request->file('image')->store('uploads/profiles', 'r2');
            $profile->image = $imagePath;
        }
        
        // Update profile data
        $profile->update([
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
        
        // Load fresh user data with profile
        $user->load('profile');
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            // Delete the current access token
            // $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }
}