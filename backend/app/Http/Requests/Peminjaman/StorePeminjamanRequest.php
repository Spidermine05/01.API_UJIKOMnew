<?php

namespace App\Http\Requests\Peminjaman;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePeminjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
        'tgl_kembali_plan' => ['required','date','date_format:Y-m-d','after:today'],
        'items' => ['required','array','min:1'],
        'items.*.alat_id' => ['required','integer','distinct',
        Rule::exists('alat','id')],
        'items.*.jumlah' => ['required','integer','min:1'],
        'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'peminjam')],
        ];
    }
    public function messages(): array
    {
        return [
            'tgl_kembali_plan.required' => 'Tanggal rencana pengembalian wajib diisi.',
            'tgl_kembali_plan.date_format' => 'Format tanggal harus Tahun-Bulan-Tahun (YYYY-MM-DD)',
            'tgl_kembali_plan.after' => 'Tanggal rencana kembali harus setelah hari ini',
            'items.required' => 'Anda harus memilih minimal satu alat untuk dipinjam.',
            'items.array' => ' Format data item yang dikirim harus berupa daftar/list',
            'items.min' => 'Anda harus memilih minimal satu alat untuk dipinjam.',
            'items.*.alat_id.distinct' => 'Alat yang sama tidak boleh dipilih lebih dari sekali. Gabungkan jumlahnya dalam satu item.',
        ];
    }
    public function attributes(): array
    {
        return [
            'items.*.alat_id' => 'Alat',
            'items.*.jumlah' => 'Jumlah Barang', 
        ];
    }
}