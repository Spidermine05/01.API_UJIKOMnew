<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = DB::transaction(function () use ($request) {
                return User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'peminjam',
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                ]);
            });

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registrasi berhasil.',
                'data' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi.',
            ], 500);

        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [
                    'Kredensial yang diberikan tidak cocok dengan data kami.'
                ],
            ]);
        }
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;
        

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => new UserResource($user),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
        public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp'        => ['nullable', 'string', 'max:20'],
            'alamat'       => ['nullable', 'string', 'max:500'],
            'foto_profile' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $data = $request->only(['name', 'email', 'no_hp', 'alamat']);

        if ($request->hasFile('foto_profile')) {
            if ($user->foto_profile) {
                Storage::disk('public')->delete($user->foto_profile);
            }
            $data['foto_profile'] = $request->file('foto_profile')->store('profiles', 'public');
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => new UserResource($user->refresh()),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password berhasil diperbarui.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Data profil berhasil diambil.',
            'data' => new UserResource($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil. Token telah dihapus.',
        ]);
    }
}