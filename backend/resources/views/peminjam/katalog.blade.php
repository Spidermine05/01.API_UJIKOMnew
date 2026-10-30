@extends('layouts.peminjam')

@section('title', 'Katalog Alat - Peminjam')

@php
    $daftarKategori = $alats->pluck('kategori.nama_kategori')->filter()->unique()->sort()->values();

    // Warna berbeda per kategori, biar gampang dibedakan sekilas mata
    $paletteWarna = [
        ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
        ['bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
        ['bg' => 'bg-pink-100', 'text' => 'text-pink-700'],
        ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-700'],
        ['bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
        ['bg' => 'bg-teal-100', 'text' => 'text-teal-700'],
    ];
    $warnaKategori = [];
    foreach ($daftarKategori as $i => $nama) {
        $warnaKategori[$nama] = $paletteWarna[$i % count($paletteWarna)];
    }
@endphp

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Katalog Alat Tersedia</h1>
        <p class="text-sm text-gray-500 mt-1">Pilih alat yang ingin dipinjam, atur jumlahnya, lalu ajukan peminjaman. Pengajuan akan diproses oleh petugas.</p>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-5" id="gridAlat">
                @forelse($alats as $alat)
                    @php $warna = $warnaKategori[$alat->kategori->nama_kategori ?? ''] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-500']; @endphp
                    <div class="kartu-alat relative flex flex-col rounded-xl border-2 border-gray-200 bg-white p-4 cursor-pointer transition
                            hover:border-gray-300 hover:shadow-sm
                            [&:has(:checked)]:border-blue-500 [&:has(:checked)]:bg-blue-50/60 [&:has(:checked)]:shadow-md"
                        data-nama="{{ strtolower($alat->nama_alat) }}"
                        data-kategori="{{ strtolower($alat->kategori->nama_kategori ?? '') }}">

                        <input type="checkbox" name="alat_id[]" value="{{ $alat->id }}" class="chk-alat peer sr-only">

                        {{-- Tanda centang saat dipilih --}}
                        <span class="absolute top-3 right-3 w-6 h-6 rounded-full border-2 border-gray-300 bg-white flex items-center justify-center
                            peer-checked:bg-blue-600 peer-checked:border-blue-600 transition pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>

                        <div class="flex items-start gap-3 mb-4 pr-6">
                           @if($alat->gambar_url)
                                    <img src="{{ $alat->gambar_url }}"
                                    class="w-20 h-20 rounded-lg object-cover flex-shrink-0 border border-gray-200">
                            @else
                                <span class="w-20 h-20 rounded-lg flex items-center justify-center flex-shrink-0 {{ $warna['bg'] }} {{ $warna['text'] }}">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                    </svg>
                                </span>
                            @endif
                            <div class="min-w-0">
                                <h4 class="font-bold text-gray-900 leading-snug">{{ $alat->nama_alat }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $alat->kategori->nama_kategori ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-between pt-3 border-t border-gray-100">
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                    {{ $alat->stok == 0 ? 'bg-red-100 text-red-800' : ($alat->stok > 10 ? 'bg-emerald-100 text-emerald-800' : ($alat->stok > 3 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')) }}">
                    {{ $alat->stok == 0 ? 'Stok Habis' : $alat->stok . ' unit' }}
                </span>
                        @if($alat->stok == 0)
                            <span class="text-xs text-red-500 font-medium">Tidak bisa dipinjam</span>
                        @else
                            <div class="flex items-center gap-1.5">
                                <button type="button" class="btn-minus w-7 h-7 rounded-md border border-gray-300 text-gray-500 font-bold hover:bg-gray-100 disabled:opacity-30 disabled:hover:bg-transparent transition" disabled>
                                    &minus;
                                </button>
                                <input type="number" name="jumlah[]" value="1" min="1" max="{{ $alat->stok }}" disabled
                                    class="input-jumlah w-9 text-center text-sm font-semibold border-0 bg-transparent focus:outline-none disabled:text-gray-300">
                                <button type="button" class="btn-plus w-7 h-7 rounded-md border border-gray-300 text-gray-500 font-bold hover:bg-gray-100 disabled:opacity-30 disabled:hover:bg-transparent transition" disabled>
                                    +
                                </button>
                            </div>
                        @endif
                    </div>
                    </div>
                @empty
                    <p class="col-span-full py-6 text-center text-gray-500">Tidak ada alat yang tersedia saat ini.</p>
                @endforelse
            </div>
            <p id="pesanKosong" class="hidden py-8 text-center text-gray-500 text-sm">
                Tidak ada alat yang cocok dengan pencarian Anda.
            </p>
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

        var counter = document.getElementById('jumlahDipilih');

        function updateCounter() {
            var total = document.querySelectorAll('.chk-alat:checked').length;
            counter.textContent = total;
        }

        // Interaksi tiap kartu: klik kartu = pilih/batal, tombol +/- atur jumlah sekaligus otomatis memilih
        document.querySelectorAll('.kartu-alat').forEach(function (kartu) {
            var chk = kartu.querySelector('.chk-alat');
            var inputJumlah = kartu.querySelector('.input-jumlah');
            var btnMinus = kartu.querySelector('.btn-minus');
            var btnPlus = kartu.querySelector('.btn-plus');

                if (parseInt(kartu.dataset.stok, 10) === 0) {
            return; // skip: stok habis, kartu tidak bisa dipilih/klik
            }

            function setTerpilih(terpilih) {
                chk.checked = terpilih;
                inputJumlah.disabled = !terpilih;
                btnMinus.disabled = !terpilih;
                btnPlus.disabled = !terpilih;
                updateCounter();
            }

            kartu.addEventListener('click', function () {
                setTerpilih(!chk.checked);
            });

            [btnMinus, btnPlus, inputJumlah].forEach(function (el) {
                el.addEventListener('click', function (e) { e.stopPropagation(); });
            });

            btnMinus.addEventListener('click', function () {
                var nilai = parseInt(inputJumlah.value, 10) || 1;
                if (nilai > 1) inputJumlah.value = nilai - 1;
            });

            btnPlus.addEventListener('click', function () {
                var maks = parseInt(inputJumlah.max, 10);
                var nilai = parseInt(inputJumlah.value, 10) || 1;
                if (nilai < maks) inputJumlah.value = nilai + 1;
            });

            inputJumlah.addEventListener('change', function () {
                var maks = parseInt(inputJumlah.max, 10);
                var nilai = parseInt(inputJumlah.value, 10);
                if (isNaN(nilai) || nilai < 1) inputJumlah.value = 1;
                if (nilai > maks) inputJumlah.value = maks;
            });
        });

        // Pencarian & filter kategori
        var inputCari = document.getElementById('searchAlat');
        var pesanKosong = document.getElementById('pesanKosong');
        var kategoriAktif = 'semua';

        function terapkanFilter() {
            var kataKunci = inputCari.value.trim().toLowerCase();
            var adaYangTampil = false;

            document.querySelectorAll('.kartu-alat').forEach(function (kartu) {
                var cocokNama = kartu.dataset.nama.includes(kataKunci);
                var cocokKategori = kategoriAktif === 'semua' || kartu.dataset.kategori === kategoriAktif;
                var tampil = cocokNama && cocokKategori;
                kartu.classList.toggle('hidden', !tampil);
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