@extends('layouts.peminjam')

@section('title', 'Riwayat Peminjaman - Peminjam')

@php
    // Label & warna badge yang ramah untuk tiap kemungkinan status di database
    $labelStatus = [
        'diajukan'     => ['Menunggu Persetujuan', 'bg-amber-100 text-amber-800'],
        'dipinjam'     => ['Sedang Dipinjam', 'bg-blue-100 text-blue-800'],
        'telat'        => ['Terlambat', 'bg-red-100 text-red-800'],
        'dikembalikan' => ['Selesai', 'bg-emerald-100 text-emerald-800'],
        'selesai'      => ['Selesai', 'bg-emerald-100 text-emerald-800'],
    ];

    $statusTersedia = $peminjamans->pluck('status')->unique()->values();

    // Warna ikon per kategori, sama seperti di halaman Katalog biar senada
    $paletteWarna = [
        ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
        ['bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
        ['bg' => 'bg-pink-100', 'text' => 'text-pink-700'],
        ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-700'],
        ['bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
        ['bg' => 'bg-teal-100', 'text' => 'text-teal-700'],
    ];
    $semuaKategori = $peminjamans->flatMap(function ($p) {
        return $p->detailPinjam->pluck('alat.kategori.nama_kategori');
    })->filter()->unique()->sort()->values();
    $warnaKategori = [];
    foreach ($semuaKategori as $i => $nama) {
        $warnaKategori[$nama] = $paletteWarna[$i % count($paletteWarna)];
    }
@endphp

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Riwayat Peminjaman Saya</h1>
        <p class="text-sm text-gray-500 mt-1">Pantau status pengajuan, alat yang sedang dipinjam, dan riwayat pengembalian Anda di sini.</p>
    </div>

    @if($peminjamans->isEmpty())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <p class="text-gray-600 font-medium">Anda belum pernah mengajukan peminjaman alat.</p>
            <p class="text-sm text-gray-400 mt-1 mb-4">Yuk, mulai pinjam alat yang Anda butuhkan dari katalog.</p>
            <a href="{{ route('peminjam.katalog') }}"
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                Lihat Katalog Alat
            </a>
        </div>
    @else

        {{-- Tab Filter Status --}}
        <div class="flex flex-wrap gap-2 mb-5" id="filterStatus">
            <button type="button" data-status="semua"
                class="status-pill px-4 py-2 rounded-full text-sm font-semibold bg-blue-600 text-white transition">
                Semua ({{ $peminjamans->count() }})
            </button>
            @foreach($statusTersedia as $status)
                @php
                    $jumlah = $peminjamans->where('status', $status)->count();
                    $label = $labelStatus[$status][0] ?? ucfirst($status);
                @endphp
                <button type="button" data-status="{{ $status }}"
                    class="status-pill px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                    {{ $label }} ({{ $jumlah }})
                </button>
            @endforeach
        </div>

        <div class="space-y-4" id="daftarRiwayat">
            @foreach($peminjamans as $p)
                @php
                    $labelInfo = $labelStatus[$p->status] ?? [ucfirst($p->status), 'bg-gray-100 text-gray-600'];
                    [$labelTeks, $labelWarna] = $labelInfo;

                    // Alat sedang dipinjam tapi lewat rencana kembali -> tandai terlambat
                    $terlambat = $p->status === 'dipinjam' && \Illuminate\Support\Carbon::parse($p->tgl_kembali_plan)->isPast();
                    if ($terlambat) {
                        $labelTeks = 'Terlambat';
                        $labelWarna = 'bg-red-100 text-red-800';
                    }

                    $selesai = in_array($p->status, ['dikembalikan', 'selesai']);
                    $langkahAktif = $selesai ? 3 : ($p->status === 'diajukan' ? 1 : 2);
                @endphp

                <div class="kartu-riwayat bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" data-status="{{ $p->status }}">
                    <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <span class="text-sm font-bold text-gray-800">Pengajuan #{{ $p->id }}</span>
                            <span class="text-xs text-gray-400 ml-2">Diajukan {{ \Illuminate\Support\Carbon::parse($p->created_at)->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $labelWarna }}">{{ $labelTeks }}</span>
                    </div>

                    <div class="p-5">
                        {{-- Progress langkah --}}
                        <div class="flex items-center mb-6 max-w-md">
                            @foreach(['Diajukan', 'Dipinjam', 'Selesai'] as $i => $teksLangkah)
                                @php $nomor = $i + 1; @endphp
                                <div class="flex items-center {{ $nomor < 3 ? 'flex-1' : '' }}">
                                    <div class="flex flex-col items-center">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                            {{ $nomor <= $langkahAktif ? ($terlambat && $nomor === 2 ? 'bg-red-500 text-white' : 'bg-blue-600 text-white') : 'bg-gray-200 text-gray-500' }}">
                                            {{ $nomor }}
                                        </div>
                                        <span class="text-[11px] text-gray-500 mt-1 whitespace-nowrap">{{ $teksLangkah }}</span>
                                    </div>
                                    @if($nomor < 3)
                                        <div class="flex-1 h-0.5 mx-1 {{ $nomor < $langkahAktif ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Alat yang Dipinjam</p>
                                <ul class="space-y-2">
                                    @forelse($p->detailPinjam as $detail)
                                        @php
                                            $namaKategori = $detail->alat->kategori->nama_kategori ?? '';
                                            $warna = $warnaKategori[$namaKategori] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-500'];
                                        @endphp
                                        <li class="flex items-center gap-3">
                                            <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $warna['bg'] }} {{ $warna['text'] }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                                </svg>
                                            </span>
                                            <span class="flex-1 text-sm text-gray-700 min-w-0 truncate">{{ $detail->alat->nama_alat ?? 'Alat sudah dihapus' }}</span>
                                            <span class="text-xs font-semibold text-gray-400 flex-shrink-0">x{{ $detail->jumlah }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-gray-400">Tidak ada data alat.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Detail Tanggal</p>
                                <dl class="text-sm text-gray-700 space-y-1">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Tanggal Pinjam</dt>
                                        <dd>{{ \Illuminate\Support\Carbon::parse($p->tgl_pinjam)->translatedFormat('d M Y') }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Rencana Kembali</dt>
                                        <dd>{{ \Illuminate\Support\Carbon::parse($p->tgl_kembali_plan)->translatedFormat('d M Y') }}</dd>
                                    </div>
                                    @if($p->pengembalian)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Tanggal Kembali</dt>
                                            <dd>{{ \Illuminate\Support\Carbon::parse($p->pengembalian->tgl_kembali)->translatedFormat('d M Y') }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Kondisi Alat</dt>
                                            <dd>{{ $p->pengembalian->kondisi_kembali }}</dd>
                                        </div>
                                        @if($p->pengembalian->denda > 0)
                                            <div class="flex justify-between text-red-600 font-semibold">
                                                <dt>Denda</dt>
                                                <dd>Rp {{ number_format($p->pengembalian->denda, 0, ',', '.') }}</dd>
                                            </div>
                                        @endif
                                    @endif
                                </dl>
                            </div>
                        </div>

                        @if($p->status === 'dipinjam')
                            <div class="mt-5">
                                @if($p->tgl_pengajuan_kembali)
                                    {{-- Sudah diajukan, menunggu petugas --}}
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 bg-amber-50 border border-amber-200 rounded-xl p-4">
                                        <span class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-amber-800">Menunggu verifikasi petugas</p>
                                            <p class="text-xs text-amber-700 mt-0.5">Diajukan pada {{ $p->tgl_pengajuan_kembali->translatedFormat('d M Y, H:i') }}. Silakan serahkan alat ke petugas.</p>
                                        </div>
                                        <form action="{{ route('peminjam.peminjaman.batalKembali', $p->id) }}" method="POST"
                                            onsubmit="return confirm('Batalkan pengajuan pengembalian ini?')" class="flex-shrink-0">
                                            @csrf
                                            <button type="submit"
                                                class="text-sm font-semibold text-amber-800 hover:text-amber-900 hover:underline whitespace-nowrap">
                                                Batalkan
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    {{-- Belum diajukan --}}
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
                                        <span class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                            </svg>
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-blue-900">Sudah selesai pakai alat ini?</p>
                                            <p class="text-xs text-blue-700 mt-0.5">Ajukan pengembalian supaya petugas tahu dan bisa segera memverifikasi.</p>
                                        </div>
                                        <form action="{{ route('peminjam.peminjaman.ajukanKembali', $p->id) }}" method="POST"
                                            onsubmit="return confirm('Ajukan pengembalian untuk alat ini? Pastikan alat sudah siap diserahkan ke petugas.')" class="flex-shrink-0">
                                            @csrf
                                            <button type="submit"
                                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition shadow-sm">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                                </svg>
                                                Ajukan Pengembalian
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <p id="pesanKosongRiwayat" class="hidden text-center text-gray-500 text-sm py-8">
            Tidak ada peminjaman dengan status ini.
        </p>
    @endif

@endsection

@push('scripts')
<script>
    (function () {
        var statusAktif = 'semua';
        var pesanKosong = document.getElementById('pesanKosongRiwayat');

        document.querySelectorAll('.status-pill').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                document.querySelectorAll('.status-pill').forEach(function (t) {
                    t.classList.remove('bg-blue-600', 'text-white');
                    t.classList.add('bg-gray-100', 'text-gray-600');
                });
                tombol.classList.remove('bg-gray-100', 'text-gray-600');
                tombol.classList.add('bg-blue-600', 'text-white');
                statusAktif = tombol.dataset.status;

                var adaYangTampil = false;
                document.querySelectorAll('.kartu-riwayat').forEach(function (kartu) {
                    var tampil = statusAktif === 'semua' || kartu.dataset.status === statusAktif;
                    kartu.classList.toggle('hidden', !tampil);
                    if (tampil) adaYangTampil = true;
                });
                if (pesanKosong) pesanKosong.classList.toggle('hidden', adaYangTampil);
            });
        });
    })();
</script>
@endpush