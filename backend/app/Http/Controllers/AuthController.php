<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //Menampilkan form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    //Mmeproses Login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            //Redirect berdasarkan role sesuai matriks anda
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'petugas') {
                return redirect()->route('petugas.peminjaman.index');
            } elseif ($user->role === 'peminjam') {
                return redirect()->route('peminjam.katalog');
            }

            Auth::logout();
            return redirect()->route('login')->with('error', 'Role tidak dikenali.');
        }
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }
    //Proses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    //Menampilkan form login
    public function showRegister()
    {
        return view('auth.register');
    }

    //Memproses Register
    public function register(Request $request)
    {
        $validated = $request->validate ([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','unique:users,email'],
            'password' => ['required','string','min:8'],
            'no_hp' => ['required','string','max:20','unique:users,no_hp']
        ],
        [
            'name.required' => 'Username Wajib diisi',
            'email.required' => 'Email Wajib diisi',
            'email.email' => 'Email Tidak Valid',
            'email.unique' => 'Email Sudah Terdaftar',
            'password.min' => 'Password Minimal 8 Karakter',
            'password.required' => 'Password Wajib diisi',
            'password.confirmed' => 'Konfirmasi Password tidak valid',
            'no_hp.required' => 'Nomor Telpon wajib diisi'
        ]);
            User::create ([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'no_hp' => $validated['no_hp'],
                'role' => 'Peminjam'            
                ]);

                return redirect()
                    ->route('login')
                    ->with('success','Registrasi Berhasil silahkan Login');

    }
}
