<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(): View
    {
        return view('produk', [
            'kategori' => Kategori::orderBy('nama')->get(['id', 'nama']),
            'daftar'   => Produk::with('kategori:id,nama')->orderBy('id')->get(),
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $id = (int) $request->input('id', 0);

        $wajib = 'Nama, stok, dan harga wajib diisi.';
        $angka = 'Stok dan harga harus berupa angka 0 atau lebih.';
        $url   = 'URL gambar harus diawali http:// atau https://';

        $data = $request->validate([
            'nama'        => ['required', 'max:150'],
            'stok'        => ['required', 'integer', 'min:0', 'max:2147483647'],
            'harga'       => ['required', 'integer', 'min:0', 'max:2147483647'],
            'gambar'      => ['nullable', 'max:500', 'regex:#^https?://#i', 'url'],
            'kategori_id' => ['required', 'integer', 'exists:kategori,id'],
        ], [
            'nama.required'  => $wajib,
            'stok.required'  => $wajib,
            'harga.required' => $wajib,
            'nama.max'       => 'Nama produk maksimal 150 karakter.',
            'stok.integer'   => $angka, 'stok.min'  => $angka, 'stok.max'  => $angka,
            'harga.integer'  => $angka, 'harga.min' => $angka, 'harga.max' => $angka,
            'gambar.max'     => $url, 'gambar.regex' => $url, 'gambar.url' => $url,
            'kategori_id.required' => 'Kategori tidak valid.',
            'kategori_id.integer'  => 'Kategori tidak valid.',
            'kategori_id.exists'   => 'Kategori tidak valid.',
        ]);

        $data['gambar'] = $data['gambar'] ?? null;

        if ($id > 0) {
            Produk::whereKey($id)->update($data);
        } else {
            Produk::create($data);
        }

        return redirect()->route('produk.index')->with('flash', ['success', 'Produk disimpan.']);
    }

    public function hapus(Request $request): RedirectResponse
    {
        try {
            Produk::whereKey((int) $request->input('id', 0))->delete();
            $flash = ['success', 'Produk dihapus.'];
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? 0) !== 1451) { // 1451 = masih dipakai tabel lain
                throw $e;
            }
            $flash = ['error', 'Produk sudah ada di data penjualan, tidak bisa dihapus.'];
        }

        return redirect()->route('produk.index')->with('flash', $flash);
    }
}
