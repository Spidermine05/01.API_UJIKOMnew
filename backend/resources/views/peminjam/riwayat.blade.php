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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Alat yang Dipinjam</p>
                                <ul class="text-sm text-gray-700 space-y-1">
                                    @forelse($p->detailPinjam as $detail)
                                        <li class="flex justify-between border-b border-dashed border-gray-100 py-1">
                                            <span>{{ $detail->alat->nama_alat ?? 'Alat sudah dihapus' }}</span>
                                            <span class="text-gray-400">x{{ $detail->jumlah }}</span>
                                        </li>
                                    @empty
                                        <li class="text-gray-400">Tidak ada data alat.</li>
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