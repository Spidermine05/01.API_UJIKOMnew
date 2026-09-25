@extends('layouts.app')

@section('title', 'Dashboard Admin - Sistem Peminjaman')
@section('header-title', 'Ringkasan Aktivitas Sistem')

@section('content')

<div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-lg shadow-sm">
    Selamat datang, <strong class="font-semibold">{{ auth()->user()->name }}</strong>! Anda login sebagai
    <span class="uppercase font-bold text-emerald-900">{{ auth()->user()->role }}</span>.
</div>
@endsection