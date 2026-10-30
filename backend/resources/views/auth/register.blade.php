<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Peminjaman Alat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
    <h3 class="text-2xl font-bold text-center text-gray-800 mb-6">Register Peminjam</h3>
    {{-- // Alert Error Validasi --}}
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                <ul class="list-disc pl-5 mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    <form action="{{ route('register.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Username</label>
                <input type="name" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Username">
            </div>
                {{-- Input Email --}}
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Email">
            </div>
                {{-- // Input Password --}}
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Konfirmasi Password</label>
                <input type="password" name="password_confirmed" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Konfirmasi Password">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nomor Telpon</label>
                <input type="tel" name="no_hp" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Nomor">
            </div>
            
                {{-- // Tombol SUbmit --}}
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                Masuk
            </button>
            <p class="block text-gray-700 text-sm font-semibold mb-6">Sudah memiliki Akun? 
                <a href="{{ route('login')}}" class="text-blue-600 font-semibold text-underline"> Login disini
                </a>
            </p>
</body>
</html>