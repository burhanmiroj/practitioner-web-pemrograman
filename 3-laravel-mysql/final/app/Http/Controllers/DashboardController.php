<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Penjualan;
use App\Models\Produk;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'kategori'   => Kategori::count(),
            'produk'     => Produk::count(),
            'stok'       => (int) Produk::sum('stok'),
            'pendapatan' => (int) Penjualan::sum('total'),
        ]);
    }
}
