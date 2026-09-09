<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Menampilkan semua pengguna
     */
    public function index(): JsonResponse
    {
        $users = User::latest()->get();

        return response()->json([
            'message' => 'Daftar Pengguna berhasil diambil',
            'data' => UserResource::collection($users)
        ]);
    }

    /**
     * Menambahkan pengguna baru
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($request, $data) {

            // Hash password
            $data['password'] = Hash::make($data['password']);

            // Upload foto profile
            if ($request->hasFile('foto_profile')) {
                $data['foto_profile'] = $request
                    ->file('foto_profile')
                    ->store('profiles', 'public');
            }

            return User::create($data);
        });

        return response()->json([
            'message' => 'Pengguna berhasil ditambahkan.',
            'data' => new UserResource($user)
        ], 201);
    }

    /**
     * Menampilkan satu pengguna
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'message' => 'Data pengguna berhasil diambil.',
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Mengubah data pengguna
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    ): JsonResponse {

        $data = collect($request->validated());

        DB::transaction(function () use ($request, $data, $user) {

            // Jika password diubah, hash password baru
            if ($data->get('password')) {
                $data['password'] = Hash::make(
                    $data['password']
                );
            } else {
                $data->forget('password');
            }

            // Jika upload foto baru
            if ($request->hasFile('foto_profile')) {

                // Hapus foto lama
                if ($user->foto_profile) {
                    Storage::disk('public')->delete(
                        $user->foto_profile
                    );
                }

                // Simpan foto baru
                $data['foto_profile'] = $request
                    ->file('foto_profile')
                    ->store('profiles', 'public');
            }

            $user->update($data->toArray());
        });

        // Refresh data user setelah update
        $user->refresh();

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui.',
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Menghapus pengguna
     */
    public function destroy(User $user): JsonResponse
    {
        DB::transaction(function () use ($user) {

            // Hapus foto profile
            if ($user->foto_profile) {
                Storage::disk('public')->delete(
                    $user->foto_profile
                );
            }

            // Hapus user
            $user->delete();
        });

        return response()->json([
            'message' => 'Pengguna berhasil dihapus.'
        ]);
    }
}