@extends('layouts.app')

@section('content')
    @php
        $cards = [
            ['Kategori', number_format($kategori, 0, ',', '.'), 'bg-sun'],
            ['Produk', number_format($produk, 0, ',', '.'), 'bg-sky'],
            ['Total stok', number_format($stok, 0, ',', '.'), 'bg-mint'],
            ['Total pendapatan', 'Rp ' . number_format($pendapatan, 0, ',', '.'), 'bg-coral'],
        ];
    @endphp
    <h1 class="font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight mb-5">Ringkasan toko</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach ($cards as [$label, $value, $warna])
            <div class="{{ $warna }} border-2 border-ink rounded-xl shadow-hard p-5">
                <p class="text-sm font-semibold">{{ $label }}</p>
                <p class="font-['Bricolage_Grotesque',sans-serif] text-4xl font-extrabold mt-2 break-words">{{ $value }}</p>
            </div>
        @endforeach
    </div>
@endsection
