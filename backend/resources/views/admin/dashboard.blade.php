@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')

<div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm">
    Selamat datang, <strong class="font-semibold">{{ auth()->user()->name }}</strong>! Anda login sebagai
    <span class="uppercase font-bold text-emerald-900">{{ auth()->user()->role }}</span>.
</div>

{{-- KARTU STATISTIK --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-xl shadow-sm">
        <p class="text-sm text-gray-500">Total Alat</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalAlat }}</p>
        <p class="text-xs text-red-500 mt-1">{{ $stokHabis }} stok habis</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm">
        <p class="text-sm text-gray-500">Total User</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalUser }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $totalPetugas }} petugas &middot; {{ $totalPeminjam }} peminjam</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm">
        <p class="text-sm text-gray-500">Peminjaman Aktif</p>
        <p class="text-3xl font-bold text-blue-600 mt-1">{{ $peminjamanAktif }}</p>
        <p class="text-xs text-gray-400 mt-1">sedang dipinjam</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm">
        <p class="text-sm text-gray-500">Telat Kembali</p>
        <p class="text-3xl font-bold text-red-600 mt-1">{{ $telatKembali }}</p>
        <p class="text-xs text-gray-400 mt-1">perlu ditindaklanjuti</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ALAT PALING SERING DIPINJAM --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-4">Alat Paling Sering Dipinjam</h3>
        <div class="space-y-4">
            @forelse($alatSering as $item)
                <div class="flex items-center justify-between">
                    <span class="text-gray-700">{{ $item->alat->nama_alat ?? '-' }}</span>
                    <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                        {{ $item->total_pinjam }}x dipinjam
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada data peminjaman.</p>
            @endforelse
        </div>
    </div>

    {{-- PEMINJAMAN MENUNGGU PERSETUJUAN --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">Peminjaman Menunggu Persetujuan</h3>
            @if($totalMenunggu > 0)
                <span class="bg-yellow-100 text-yellow-700 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">
                    {{ $totalMenunggu }}
                </span>
            @endif
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($menungguPersetujuan as $p)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $p->user->name ?? '-' }}</p>
                        <p class="text-sm text-gray-500">
                            @foreach($p->detailPinjam as $d)
                                {{ $d->alat->nama_alat ?? '-' }} ({{ $d->jumlah }}){{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </p>
                    </div>
                    <span class="text-xs text-gray-400">Diajukan {{ $p->created_at->translatedFormat('d M') }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-3">Tidak ada pengajuan menunggu.</p>
            @endforelse
        </div>
        <a href="{{ route('admin.peminjaman.index', ['status' => 'diajukan']) }}" class="block text-center text-blue-600 text-sm font-medium mt-3">
            Lihat semua &rarr;
        </a>
    </div>

</div>

@endsection