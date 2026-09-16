<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-gray-900 text-white flex flex-col hidden md:flex">
            <div class="p-5 text-xl font-bold tracking-wider border-b border-gray-800">
                PANEL {{ strtoupper(auth()->user()->role) }}
            </div>
            <nav class="flex-1 p-4 space-y-2">
                {{-- MENU KHUSUS ADMIN --}}
                {{-- Menu Dashboard --}}
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}"
                    class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.user.index') }}"
                class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.user*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Kelola User</a>
                <a href="{{ route('admin.kategori.index') }}"
                class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.kategori*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Kelola Kategori</a>
                <a href="{{ route('admin.alat.index') }}"
                class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.alat*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Kelola Alat</a>
                <a href="{{ route('admin.peminjaman.index') }}"
                class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('admin.peminjaman*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">Kelola Peminjaman</a>
                @if(Route::has('admin.pengembalian.index'))
                    <a href="{{ route('admin.pengembalian.index') }}"
                        class="block px-4 py-2 rounded-lg transition
                            {{ request()->routeIs('admin.pengembalian*') ?
                            'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            Kelola Pengembalian</a>
                @endif
                @endif
                {{-- MENU KHUSUS PETUGAS --}}

                @if(auth()->user()->role == 'petugas')

                <a href="{{ route('petugas.peminjaman.index') }}"
                class="block px-4 py-2 rounded-lg transition {{
                    request()->routeIs('petugas.peminjaman*')
                        ? 'bg-gray-800 text-white font-medium shadow'
                        : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                }}">
                Persetujuan Peminjaman
                </a>

                <a href="{{ route('petugas.pengembalian.index') }}"
                class="block px-4 py-2 rounded-lg transition {{
                    request()->routeIs('petugas.pengembalian*')
                        ? 'bg-gray-800 text-white font-medium shadow'
                        : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                }}">
                Pemantauan Pengembalian
                </a>

                @if(Route::has('petugas.laporan.index'))
                    <a href="{{ route('petugas.laporan.index') }}"
                    class="block px-4 py-2 rounded-lg transition {{
                        request()->routeIs('petugas.laporan*')
                            ? 'bg-gray-800 text-white font-medium shadow'
                            : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                    }}">
                    Cetak Laporan
                    </a>
                @endif

                @endif

                {{-- MENU BERSAMA: Profil Saya (admin & petugas) --}}
                {{-- <div class="pt-2 mt-2 border-t border-gray-800">
                    <a href="{{ route('profile.edit') }}"
                        class="block px-4 py-2 rounded-lg transition {{ request()->routeIs('profile.*') ? 'bg-gray-800 text-white font-medium shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        Profil Saya
                    </a>
                </div> --}}
            </nav>
            <div class="p-4 border-t border-gray-800">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 group">
                    @if(auth()->user()->foto_profile)
                        <img src="{{ asset(auth()->user()->foto_profile) }}" alt="Foto Profil"
                            class="w-9 h-9 rounded-full object-cover border-2 border-gray-700 group-hover:border-gray-500 transition">
                    @else
                        <span class="w-9 h-9 rounded-full bg-gray-700 text-white text-sm font-bold flex items-center justify-center border-2 border-gray-700 group-hover:border-gray-500 transition">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm text-white font-semibold truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                </a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-y-auto">
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 z-10">
                <div class="text-lg font-semibold text-gray-800">
                    @yield('header-title', 'Dasboard')
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('profile.edit') }}"
                        class="text-sm font-medium text-gray-500 hover:text-gray-800 transition hidden sm:inline">
                        Profil Saya
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>