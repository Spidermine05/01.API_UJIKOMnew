@extends(auth()->user()->role === 'peminjam' ? 'layouts.peminjam' : 'layouts.app')

@section('title', 'Profil Saya')
@section('header-title', 'Profil Saya')

@section('content')

    @if(auth()->user()->role !== 'peminjam')
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
                Terjadi kesalahan, mohon periksa kembali isian Anda di bawah ini.
            </div>
        @endif
    @endif

    {{-- Kartu ringkasan profil --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 flex flex-col sm:flex-row items-center sm:items-start gap-5">
        @if($user->foto_profile)
            <img id="previewFoto" src="{{ asset($user->foto_profile) }}" alt="Foto Profil"
                class="w-20 h-20 rounded-full object-cover border-4 border-gray-100">
        @else
            <span id="previewFoto" class="w-20 h-20 rounded-full bg-blue-600 text-white text-2xl font-bold flex items-center justify-center border-4 border-gray-100">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </span>
        @endif
        <div class="text-center sm:text-left">
            <h1 class="text-xl font-bold text-gray-900">{{ $user->name }}</h1>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
            <span class="inline-block mt-2 px-2.5 py-1 text-xs font-semibold rounded-full
                @if($user->role == 'admin') bg-purple-100 text-purple-800
                @elseif($user->role == 'petugas') bg-blue-100 text-blue-800
                @else bg-green-100 text-green-800 @endif">
                {{ ucfirst($user->role) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Form data diri --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi Akun</h2>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Foto Profil</label>
                    <div class="flex items-center gap-3">
                        <input type="file" name="foto_profile" id="inputFoto" accept="image/png, image/jpeg, image/jpg"
                            class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:text-sm file:font-semibold hover:file:bg-blue-100">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Format JPG/PNG, maksimal 2MB.</p>
                    @error('foto_profile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">No. HP</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('no_hp') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Alamat</label>
                    <textarea name="alamat" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('alamat', $user->alamat) }}</textarea>
                    @error('alamat') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Form ganti password --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-1">Ganti Password</h2>
            <p class="text-xs text-gray-400 mb-4">Gunakan password yang kuat dan tidak dipakai di tempat lain.</p>

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Password Saat Ini</label>
                    <input type="password" name="current_password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('current_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Password Baru</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Minimal 8 karakter.</p>
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition">
                        Perbarui Password
                    </button>
                </div>
            </form>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    // Pratinjau foto profil sebelum diunggah
    var inputFoto = document.getElementById('inputFoto');
    if (inputFoto) {
        inputFoto.addEventListener('change', function () {
            var file = inputFoto.files[0];
            if (!file) return;

            var preview = document.getElementById('previewFoto');
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.createElement('img');
                img.id = 'previewFoto';
                img.src = e.target.result;
                img.alt = 'Foto Profil';
                img.className = 'w-20 h-20 rounded-full object-cover border-4 border-gray-100';
                preview.replaceWith(img);
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endpush