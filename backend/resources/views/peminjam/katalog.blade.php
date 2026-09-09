@extends('layouts.peminjam')

@section('title', 'Katalog Alat - Peminjam')

@php
    $daftarKategori = $alats->pluck('kategori.nama_kategori')->filter()->unique()->sort()->values();
@endphp

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Katalog Alat Tersedia</h1>
        <p class="text-sm text-gray-500 mt-1">Pilih alat yang ingin dipinjam, tentukan jumlahnya, lalu ajukan peminjaman. Pengajuan akan diproses oleh petugas.</p>
    </div>

    <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST" id="formPeminjaman">
        @csrf

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 mb-5">
            <label for="tgl_kembali_plan" class="block text-sm font-semibold text-gray-700 mb-2">
                Rencana Tanggal Kembali
            </label>
            <input type="date" name="tgl_kembali_plan" id="tgl_kembali_plan" required
                class="w-full md:w-72 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-400 mt-1">Tentukan kapan alat akan Anda kembalikan.</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-800">Daftar Alat</h3>

                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <input type="text" id="searchAlat" placeholder="Cari nama alat..."
                        class="w-full sm:w-64 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            @if($daftarKategori->count() > 1)
                <div class="px-5 pt-4 flex flex-wrap gap-2" id="filterKategori">
                    <button type="button" data-kategori="semua"
                        class="kategori-pill px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-600 text-white transition">
                        Semua Kategori
                    </button>
                    @foreach($daftarKategori as $kategori)
                        <button type="button" data-kategori="{{ strtolower($kategori) }}"
                            class="kategori-pill px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                            {{ $kategori }}
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="overflow-x-auto mt-2">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                            <th class="py-3 px-4 border-b w-12">Pilih</th>
                            <th class="py-3 px-4 border-b">Nama Alat</th>
                            <th class="py-3 px-4 border-b">Kategori</th>
                            <th class="py-3 px-4 border-b">Stok Tersedia</th>
                            <th class="py-3 px-4 border-b w-40">Jumlah Pinjam</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm" id="tabelAlat">
                        @forelse($alats as $alat)
                            <tr class="baris-alat hover:bg-gray-50 transition"
                                data-nama="{{ strtolower($alat->nama_alat) }}"
                                data-kategori="{{ strtolower($alat->kategori->nama_kategori ?? '') }}">
                                <td class="py-3 px-4 border-b text-center align-middle">
                                    <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}"
                                        class="chk-alat w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                </td>
                                <td class="py-3 px-4 border-b font-medium text-gray-900 align-middle">
                                    {{ $alat->nama_alat }}
                                </td>
                                <td class="py-3 px-4 border-b align-middle">
                                    {{ $alat->kategori->nama_kategori ?? '-' }}
                                </td>
                                <td class="py-3 px-4 border-b align-middle">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                        {{ $alat->stok > 10 ? 'bg-emerald-100 text-emerald-800' : ($alat->stok > 3 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $alat->stok }} unit
                                    </span>
                                </td>
                                <td class="py-3 px-4 border-b align-middle">
                                    <input type="number" name="jumlah[]" value="1" min="1" max="{{ $alat->stok }}" disabled
                                        class="input-jumlah w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-400">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-500">Tidak ada alat yang tersedia saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <p id="pesanKosong" class="hidden py-8 text-center text-gray-500 text-sm">
                    Tidak ada alat yang cocok dengan pencarian Anda.
                </p>
            </div>
        </div>

        <div id="pesanValidasi" class="hidden mt-4 bg-red-50 border border-red-200 text-red-700 text-sm p-3 rounded-lg">
            Pilih minimal satu alat sebelum mengajukan peminjaman.
        </div>

        <div class="sticky bottom-4 mt-5">
            <div class="bg-white border border-gray-200 shadow-md rounded-lg p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-sm text-gray-600">
                    <span id="jumlahDipilih" class="font-bold text-blue-600">0</span> alat dipilih
                </span>
                <button type="submit"
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition shadow-sm">
                    Ajukan Peminjaman
                </button>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    (function () {
        // Tanggal kembali minimal besok
        var inputTanggal = document.getElementById('tgl_kembali_plan');
        var besok = new Date();
        besok.setDate(besok.getDate() + 1);
        inputTanggal.min = besok.toISOString().split('T')[0];

        // Checkbox <-> input jumlah
        var counter = document.getElementById('jumlahDipilih');

        function updateCounter() {
            var total = document.querySelectorAll('.chk-alat:checked').length;
            counter.textContent = total;
        }

        document.querySelectorAll('.chk-alat').forEach(function (chk) {
            chk.addEventListener('change', function () {
                var baris = chk.closest('tr');
                var inputJumlah = baris.querySelector('.input-jumlah');
                inputJumlah.disabled = !chk.checked;
                if (chk.checked) {
                    inputJumlah.focus();
                }
                updateCounter();
            });
        });

        // Klik baris (di luar input) ikut men-toggle checkbox agar lebih mudah dipakai
        document.querySelectorAll('.baris-alat').forEach(function (baris) {
            baris.addEventListener('click', function (e) {
                if (e.target.closest('input')) return;
                var chk = baris.querySelector('.chk-alat');
                chk.checked = !chk.checked;
                chk.dispatchEvent(new Event('change'));
            });
        });

        // Batasi jumlah pinjam agar tidak melebihi stok
        document.querySelectorAll('.input-jumlah').forEach(function (input) {
            input.addEventListener('change', function () {
                var maks = parseInt(input.max, 10);
                var nilai = parseInt(input.value, 10);
                if (isNaN(nilai) || nilai < 1) input.value = 1;
                if (nilai > maks) input.value = maks;
            });
        });

        // Pencarian & filter kategori
        var inputCari = document.getElementById('searchAlat');
        var pesanKosong = document.getElementById('pesanKosong');
        var kategoriAktif = 'semua';

        function terapkanFilter() {
            var kataKunci = inputCari.value.trim().toLowerCase();
            var adaYangTampil = false;

            document.querySelectorAll('.baris-alat').forEach(function (baris) {
                var cocokNama = baris.dataset.nama.includes(kataKunci);
                var cocokKategori = kategoriAktif === 'semua' || baris.dataset.kategori === kategoriAktif;
                var tampil = cocokNama && cocokKategori;
                baris.classList.toggle('hidden', !tampil);
                if (tampil) adaYangTampil = true;
            });

            pesanKosong.classList.toggle('hidden', adaYangTampil);
        }

        inputCari.addEventListener('input', terapkanFilter);

        document.querySelectorAll('.kategori-pill').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                document.querySelectorAll('.kategori-pill').forEach(function (t) {
                    t.classList.remove('bg-blue-600', 'text-white');
                    t.classList.add('bg-gray-100', 'text-gray-600');
                });
                tombol.classList.remove('bg-gray-100', 'text-gray-600');
                tombol.classList.add('bg-blue-600', 'text-white');
                kategoriAktif = tombol.dataset.kategori;
                terapkanFilter();
            });
        });

        // Validasi ramah sebelum submit
        var form = document.getElementById('formPeminjaman');
        var pesanValidasi = document.getElementById('pesanValidasi');
        form.addEventListener('submit', function (e) {
            var total = document.querySelectorAll('.chk-alat:checked').length;
            if (total === 0) {
                e.preventDefault();
                pesanValidasi.classList.remove('hidden');
                pesanValidasi.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                pesanValidasi.classList.add('hidden');
            }
        });
    })();
</script>
@endpush