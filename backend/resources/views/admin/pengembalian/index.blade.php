@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Dashboard Admin')
@section('header-title', 'Pemantauan Pengembalian Alat')

@section('content')
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm text-sm">
        {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 p-4 rounded-lg shadow-sm text-sm">
        {{ session('error') }}
        </div>
    @endif

    {{-- SEDANG DIPINJAM / PERLU DIKEMBALIKAN --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 mb-6">
        <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <h3 class="text-lg font-bold text-gray-800">Sedang Dipinjam</h3>
            <form action="{{ route('admin.pengembalian.index') }}" method="GET" class="flex w-full md:w-80">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama peminjam..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 text-sm font-semibold rounded-r-lg transition">
                    Cari
                </button>
                @if($search ?? false)
                    <a href="{{ route('admin.pengembalian.index') }}" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 text-sm rounded-lg flex items-center transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Detail Alat</th>
                        <th class="py-3 px-4 border-b">Rencana Kembali</th>
                        <th class="py-3 px-4 border-b">Status</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($dipinjam as $item)
                        @php
                            $terlambat = $item->tgl_kembali_plan && \Illuminate\Support\Carbon::parse($item->tgl_kembali_plan)->isPast();
                        @endphp
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $item->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    @foreach($item->detailPinjam as $detail)
                                        <li>
                                            <span class="font-semibold">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</span>
                                            (Jumlah: {{ $detail->jumlah }})
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b">{{ $item->tgl_kembali_plan }}</td>
                            <td class="py-3 px-4 border-b">
                                @if($terlambat)
                                    <span class="text-xs font-semibold text-red-700 bg-red-50 px-2.5 py-1 rounded">Terlambat</span>
                                @else
                                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded">Dipinjam</span>
                                @endif
                            </td>
                                                        <td class="py-3 px-4 border-b">
                                @if($terlambat)
                                    <span class="text-xs font-semibold text-red-700 bg-red-50 px-2.5 py-1 rounded">Terlambat</span>
                                @else
                                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded">Dipinjam</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 border-b">
                                <button type="button" onclick="document.getElementById('modalProses{{ $item->id }}').classList.remove('hidden')"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs font-semibold transition shadow-sm">
                                    Proses Pengembalian
                                </button>
                            </td>
                        </tr>

                        {{-- Modal Proses Pengembalian --}}
                        <div id="modalProses{{ $item->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
                                <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-gray-800">Proses Pengembalian - {{ $item->user->name ?? 'User Dihapus' }}</h3>
                                    <button type="button" onclick="document.getElementById('modalProses{{ $item->id }}').classList.add('hidden')"
                                        class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                                </div>
                                <form action="{{ route('admin.pengembalian.proses', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Catat pengembalian alat ini? Stok akan dikembalikan otomatis.')">
                                    @csrf
                                    <div class="p-5 space-y-3">
                                        @foreach($item->detailPinjam as $detail)
                                            <div class="flex items-center gap-3 border border-gray-100 rounded-lg p-2">
                                                @if($detail->alat && $detail->alat->gambar_url)
                                                    <img src="{{ $detail->alat->gambar_url }}" class="w-14 h-14 rounded object-cover border border-gray-200">
                                                @else
                                                    <span class="w-14 h-14 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs">No Img</span>
                                                @endif
                                                <div>
                                                    <p class="font-semibold text-sm text-gray-900">{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }}</p>
                                                    <p class="text-xs text-gray-500">Jumlah dipinjam: {{ $detail->jumlah }}</p>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kondisi Alat</label>
                                            <select name="kondisi_kembali" required
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                                <option value="">-- Pilih Kondisi --</option>
                                                <option value="Baik">Baik</option>
                                                <option value="Rusak Ringan">Rusak Ringan</option>
                                                <option value="Rusak Sedang">Rusak Sedang</option>
                                                <option value="Rusak Berat">Rusak Berat</option>
                                                <option value="Hilang">Hilang</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Denda (Rp) &mdash; opsional</label>
                                            <input type="number" name="denda" min="0" placeholder="Kosongkan jika tidak ada denda"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        </div>
                                    </div>
                                    <div class="p-5 border-t border-gray-200 flex justify-end gap-2">
                                        <button type="button" onclick="document.getElementById('modalProses{{ $item->id }}').classList.add('hidden')"
                                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 text-sm rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition">
                                            Simpan Pengembalian
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">Tidak ada alat yang sedang dipinjam.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- RIWAYAT PENGEMBALIAN --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Riwayat Pengembalian Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="py-3 px-4 border-b">Peminjam</th>
                        <th class="py-3 px-4 border-b">Detail Alat</th>
                        <th class="py-3 px-4 border-b">Tgl Kembali</th>
                        <th class="py-3 px-4 border-b">Kondisi</th>
                        <th class="py-3 px-4 border-b">Denda</th>
                        <th class="py-3 px-4 border-b">Diproses Oleh</th>
                        <th class="py-3 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    @forelse($riwayat as $r)
                        <tr class="hover:bg-gray-50 transition align-top">
                            <td class="py-3 px-4 border-b font-medium text-gray-900">
                                {{ $r->peminjaman->user->name ?? 'User Dihapus' }}
                            </td>
                            <td class="py-3 px-4 border-b">
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    @foreach($r->peminjaman->detailPinjam ?? [] as $detail)
                                        <li>{{ $detail->alat->nama_alat ?? 'Alat Dihapus' }} ({{ $detail->jumlah }})</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-4 border-b">{{ $r->tgl_kembali }}</td>
                            <td class="py-3 px-4 border-b">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded
                                    {{ $r->kondisi_kembali == 'Baik' ? 'text-emerald-700 bg-emerald-50' : 'text-amber-700 bg-amber-50' }}">
                                    {{ $r->kondisi_kembali }}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-b">
                                {{ $r->denda > 0 ? 'Rp ' . number_format($r->denda, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-4 border-b">{{ $r->petugas->name ?? '-' }}</td>
                                                        <td class="py-3 px-4 border-b">{{ $r->petugas->name ?? '-' }}</td>
                            <td class="py-3 px-4 border-b">
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="document.getElementById('modalEdit{{ $r->id }}').classList.remove('hidden')"
                                        class="bg-amber-500 hover:bg-amber-600 text-white px-2.5 py-1 rounded text-xs font-semibold transition">Edit</button>
                                    <form action="{{ route('admin.pengembalian.destroy', $r->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus data pengembalian ini? Stok akan dikurangi lagi dan status peminjaman kembali ke Dipinjam.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-2.5 py-1 rounded text-xs font-semibold transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Modal Edit Pengembalian --}}
                        <div id="modalEdit{{ $r->id }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                            <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
                                <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-gray-800">Edit Pengembalian - {{ $r->peminjaman->user->name ?? 'User Dihapus' }}</h3>
                                    <button type="button" onclick="document.getElementById('modalEdit{{ $r->id }}').classList.add('hidden')"
                                        class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                                </div>
                                <form action="{{ route('admin.pengembalian.update', $r->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="p-5 space-y-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kondisi Alat</label>
                                            <select name="kondisi_kembali" required
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                                @foreach(['Baik','Rusak Ringan','Rusak Sedang','Rusak Berat','Hilang'] as $opsi)
                                                    <option value="{{ $opsi }}" {{ $r->kondisi_kembali == $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Denda (Rp)</label>
                                            <input type="number" name="denda" min="0" value="{{ $r->denda }}"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        </div>
                                    </div>
                                    <div class="p-5 border-t border-gray-200 flex justify-end gap-2">
                                        <button type="button" onclick="document.getElementById('modalEdit{{ $r->id }}').classList.add('hidden')"
                                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition">Batal</button>
                                        <button type="submit"
                                            class="px-4 py-2 text-sm rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold transition">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-gray-500">Belum ada riwayat pengembalian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection