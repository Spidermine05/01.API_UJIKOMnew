<?php

namespace App\Observers;

use App\Models\User;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    private function catatLog(string $pesan): void
    {
        if (Auth::check()) {
            LogAktivitas::create([
                'user_id' => Auth::id(),
                'aktivitas' => $pesan,
            ]);
        }
    }

    public function created(User $user): void
    {
        $this->catatLog("Menambahkan pengguna baru: {$user->name} (Role: {$user->role})");
    }

    public function updated(User $user): void
    {
        $perubahan = array_diff(array_keys($user->getChanges()), ['updated_at', 'remember_token']);

        if (in_array('password', $perubahan, true)) {
            $this->catatLog("{$user->name} memperbarui password akun");
            $perubahan = array_diff($perubahan, ['password']);
        }

        if (!empty($perubahan)) {
            $kolom = implode(', ', $perubahan);
            $this->catatLog("{$user->name} memperbarui data profil (Kolom: {$kolom})");
        }
    }

    public function deleted(User $user): void
    {
        $this->catatLog("Menghapus akun pengguna: {$user->name}");
    }

    public function restored(User $user): void
    {
        //
    }

    public function forceDeleted(User $user): void
    {
        //
    }
}