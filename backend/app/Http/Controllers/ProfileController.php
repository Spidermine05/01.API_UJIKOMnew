<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Menampilkan halaman profil (dipakai oleh admin, petugas, & peminjam)
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    // Memperbarui data diri & foto profil
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp'        => 'nullable|string|max:20',
            'alamat'       => 'nullable|string|max:500',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'foto_profile.image' => 'File yang diunggah harus berupa gambar.',
            'foto_profile.max'   => 'Ukuran foto maksimal 2MB.',
        ]);

        $data = $request->only(['name', 'email', 'no_hp', 'alamat']);

        if ($request->hasFile('foto_profile')) {
            // Hapus foto lama supaya tidak menumpuk file yatim
            if ($user->foto_profile && file_exists(public_path($user->foto_profile))) {
                unlink(public_path($user->foto_profile));
            }

            $file     = $request->file('foto_profile');
            $filename = time() . '-' . preg_replace('/\s+/', '-', $file->getClientOriginalName());
            $file->move(public_path('storage/profil'), $filename);
            $data['foto_profile'] = 'storage/profil/' . $filename;
        }

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    // Mengganti password akun
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
            'password.confirmed'                => 'Konfirmasi password baru tidak cocok.',
            'password.min'                       => 'Password baru minimal 8 karakter.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password berhasil diperbarui.');
    }
}