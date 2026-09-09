@extends('layouts.app')

@section('title', 'Tambah Peminjaman - Panel Admin')
@section('header-title', 'Form tambah Transaksi Peminjaman')

@section('content')
<div class="max-w-2xl bg-white rounded-lg shadow-sm border border-gray-200 p-6">

    @if (session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.peminjaman.store') }}" method="POST">
        @csrf

        {{-- ================= USER ================= --}}
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Pilih Peminjam (User)
            </label>

            <div class="relative">
                <input
                    type="text"
                    id="user-search"
                    placeholder="Cari nama atau email user..."
                    autocomplete="off"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id') }}">

                <div
                    id="user-results"
                    class="absolute z-50 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden shadow-lg max-h-48 overflow-y-auto"
                >
                    @foreach ($users as $user)
                        <div
                            class="user-option px-3 py-2 cursor-pointer hover:bg-blue-50"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                        >
                            <div class="font-medium text-gray-800">
                                {{ $user->name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $user->email }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        {{-- ================= TANGGAL ================= --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Tanggal Pinjam
                </label>

                <input
                    type="date"
                    name="tgl_pinjam"
                    value="{{ old('tgl_pinjam', date('Y-m-d')) }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">
                    Rencana Tanggal Kembali
                </label>

                <input
                    type="date"
                    name="tgl_kembali_plan"
                    value="{{ old('tgl_kembali_plan', date('Y-m-d', strtotime('+3 days'))) }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>
        </div>


        {{-- ================= ALAT ================= --}}
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-semibold mb-2">
                Daftar Alat yang Dipinjam
            </label>

            <div id="alat-container" class="space-y-3">

                <div class="flex items-center gap-2 alat-row">

                    <div class="relative flex-1">

                        <input
                            type="text"
                            placeholder="Cari nama alat..."
                            autocomplete="off"
                            class="alat-search w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >

                        <input
                            type="hidden"
                            name="alat_id[]"
                            class="alat-id"
                        >

                        <div
                            class="alat-results absolute z-40 w-full bg-white border border-gray-300 rounded-lg mt-1 hidden shadow-lg max-h-48 overflow-y-auto"
                        >
                            @foreach ($alats as $alat)
                                <div
                                    class="alat-option px-3 py-2 cursor-pointer hover:bg-blue-50"
                                    data-id="{{ $alat->id }}"
                                    data-name="{{ $alat->nama_alat }}"
                                    data-stok="{{ $alat->stok }}"
                                >
                                    <div class="font-medium text-gray-800">
                                        {{ $alat->nama_alat }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Stok: {{ $alat->stok }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>

                    <input
                        type="number"
                        name="jumlah[]"
                        value="1"
                        min="1"
                        placeholder="Jumlah"
                        required
                        class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none"
                    >

                    <button
                        type="button"
                        onclick="removeRow(this)"
                        class="bg-red-500 text-white px-3 py-2 rounded-lg text-sm hover:bg-red-600 transition"
                    >
                        X
                    </button>

                </div>

            </div>

            <button
                type="button"
                onclick="addRow()"
                class="mt-3 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold px-3 py-2 rounded-lg transition"
            >
                + Tambah Alat Lain
            </button>
        </div>


        {{-- ================= BUTTON ================= --}}
        <div class="flex justify-end space-x-2">

            <a
                href="{{ route('admin.peminjaman.index') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition"
            >
                Batal
            </a>

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
            >
                Simpan Peminjaman
            </button>

        </div>

    </form>
</div>


<script>

    /* =========================
       SEARCH USER
    ========================= */

    const userSearch = document.getElementById('user-search');
    const userResults = document.getElementById('user-results');
    const userId = document.getElementById('user_id');

    userSearch.addEventListener('focus', function () {
        userResults.classList.remove('hidden');
    });

    userSearch.addEventListener('input', function () {

        const keyword = this.value.toLowerCase().trim();

        const options = document.querySelectorAll('.user-option');

        let found = false;

        options.forEach(option => {

            const name = option.dataset.name.toLowerCase();
            const email = option.dataset.email.toLowerCase();

            if (
                name.includes(keyword) ||
                email.includes(keyword)
            ) {
                option.classList.remove('hidden');
                found = true;
            } else {
                option.classList.add('hidden');
            }

        });

        userResults.classList.toggle('hidden', !found);
    });


    document.querySelectorAll('.user-option').forEach(option => {

        option.addEventListener('click', function () {

            userSearch.value =
                `${this.dataset.name} (${this.dataset.email})`;

            userId.value = this.dataset.id;

            userResults.classList.add('hidden');

        });

    });


    /* =========================
       SEARCH ALAT
    ========================= */

    function setupAlatSearch(row) {

        const searchInput = row.querySelector('.alat-search');
        const results = row.querySelector('.alat-results');
        const hiddenId = row.querySelector('.alat-id');

        searchInput.addEventListener('focus', function () {
            results.classList.remove('hidden');
        });

        searchInput.addEventListener('input', function () {

            const keyword = this.value.toLowerCase().trim();

            const options = row.querySelectorAll('.alat-option');

            let found = false;

            options.forEach(option => {

                const name = option.dataset.name.toLowerCase();

                if (name.includes(keyword)) {

                    option.classList.remove('hidden');
                    found = true;

                } else {

                    option.classList.add('hidden');

                }

            });

            results.classList.toggle('hidden', !found);

            // Reset ID jika user mengubah pencarian
            hiddenId.value = '';

        });


        row.querySelectorAll('.alat-option').forEach(option => {

            option.addEventListener('click', function () {

                searchInput.value =
                    `${this.dataset.name} (Stok: ${this.dataset.stok})`;

                hiddenId.value = this.dataset.id;

                results.classList.add('hidden');

            });

        });

    }


    /* =========================
       TAMBAH ALAT
    ========================= */

    function addRow() {

        const container = document.getElementById('alat-container');

        const firstRow = container.querySelector('.alat-row');

        const newRow = firstRow.cloneNode(true);

        // Reset input
        newRow.querySelector('.alat-search').value = '';

        newRow.querySelector('.alat-id').value = '';

        newRow.querySelector('input[name="jumlah[]"]').value = '1';

        // Reset hasil pencarian
        newRow.querySelector('.alat-results').classList.add('hidden');

        // Hilangkan state hidden dari option
        newRow.querySelectorAll('.alat-option').forEach(option => {
            option.classList.remove('hidden');
        });

        container.appendChild(newRow);

        setupAlatSearch(newRow);
    }


    /* =========================
       HAPUS ALAT
    ========================= */

    function removeRow(button) {

        const rows = document.querySelectorAll('.alat-row');

        if (rows.length > 1) {

            button.closest('.alat-row').remove();

        } else {

            alert('Minimal harus ada 1 alat yang dipilih.');

        }

    }


    /* =========================
       INITIALIZE
    ========================= */

    document.querySelectorAll('.alat-row').forEach(row => {
        setupAlatSearch(row);
    });


    /* =========================
       KLIK DI LUAR SEARCH
    ========================= */

    document.addEventListener('click', function (event) {

        // User
        if (
            !userSearch.contains(event.target) &&
            !userResults.contains(event.target)
        ) {
            userResults.classList.add('hidden');
        }

        // Alat
        document.querySelectorAll('.alat-row').forEach(row => {

            const search = row.querySelector('.alat-search');
            const results = row.querySelector('.alat-results');

            if (
                !search.contains(event.target) &&
                !results.contains(event.target)
            ) {
                results.classList.add('hidden');
            }

        });

    });

</script>

@endsection