<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Peminjam - Sistem Peminjaman Alat')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen">

    <nav class="bg-blue-600 shadow-sm sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-8">
                    <span class="text-white font-bold text-lg tracking-wide">Peminjaman Alat</span>

                    <div class="hidden md:flex items-center gap-1">
                        <a href="{{ route('peminjam.katalog') }}"
                            class="px-3 py-2 rounded-md text-sm font-medium transition
                            {{ request()->routeIs('peminjam.katalog') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-500 hover:text-white' }}">
                            Katalog Alat
                        </a>
                        <a href="{{ route('peminjam.riwayat') }}"
                            class="px-3 py-2 rounded-md text-sm font-medium transition
                            {{ request()->routeIs('peminjam.riwayat') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-500 hover:text-white' }}">
                            Riwayat Peminjaman
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 group">
                        @if(auth()->user()->foto_profile)
                            <img src="{{ asset(auth()->user()->foto_profile) }}" alt="Foto Profil"
                                class="w-8 h-8 rounded-full object-cover border-2 border-blue-400 group-hover:border-white transition">
                        @else
                            <span class="w-8 h-8 rounded-full bg-blue-800 text-white text-xs font-bold flex items-center justify-center border-2 border-blue-400 group-hover:border-white transition">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        @endif
                        <span class="hidden sm:inline text-sm font-medium text-blue-50 group-hover:text-white transition">
                            {{ auth()->user()->name }}
                        </span>
                    </a>
                    <button type="button" id="btnBukaLogout"
                        class="text-sm font-medium text-blue-100 hover:text-white border border-blue-400 hover:border-white px-3 py-1.5 rounded-md transition">
                        Keluar
                    </button>
                </div>
            </div>

            {{-- Menu untuk layar kecil --}}
            <div class="md:hidden flex gap-1 pb-3">
                <a href="{{ route('peminjam.katalog') }}"
                    class="flex-1 text-center px-3 py-2 rounded-md text-sm font-medium transition
                    {{ request()->routeIs('peminjam.katalog') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-500 hover:text-white' }}">
                    Katalog
                </a>
                <a href="{{ route('peminjam.riwayat') }}"
                    class="flex-1 text-center px-3 py-2 rounded-md text-sm font-medium transition
                    {{ request()->routeIs('peminjam.riwayat') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-500 hover:text-white' }}">
                    Riwayat
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-6 pb-16">
        @if(session('success'))
            <div class="mb-4 flex items-start gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
                <span class="font-semibold">Berhasil.</span> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
                <span class="font-semibold">Gagal.</span> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Modal Konfirmasi Logout --}}
    <div id="modalLogout" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
        <div id="overlayLogout" class="absolute inset-0 bg-gray-900/50"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-sm p-6">
            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Keluar dari akun?</h3>
                    <p class="text-sm text-gray-500 mt-1">Anda perlu login kembali untuk membuka katalog dan riwayat peminjaman.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" id="btnBatalLogout"
                    class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    Batal
                </button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                        Ya, Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('modalLogout');
            var overlay = document.getElementById('overlayLogout');
            var btnBuka = document.getElementById('btnBukaLogout');
            var btnBatal = document.getElementById('btnBatalLogout');

            function bukaModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            function tutupModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }

            btnBuka.addEventListener('click', bukaModal);
            btnBatal.addEventListener('click', tutupModal);
            overlay.addEventListener('click', tutupModal);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') tutupModal();
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>