<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

class PenjualanController extends Controller
{
    public function index(): View
    {
        return view('penjualan', [
            'produk' => Produk::orderBy('nama')->get(['id', 'nama', 'stok', 'harga']),
            'daftar' => Penjualan::with('produk:id,nama')->orderBy('id')->get(),
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $id  = (int) $request->input('id', 0);
        $qtyMsg = 'Qty harus lebih dari 0.';

        $data = $request->validate([
            'qty'       => ['required', 'integer', 'min:1', 'max:2147483647'],
            'produk_id' => ['required', 'integer'],
        ], [
            'qty.required' => $qtyMsg, 'qty.integer' => $qtyMsg, 'qty.min' => $qtyMsg, 'qty.max' => $qtyMsg,
            'produk_id.required' => 'Produk tidak ditemukan.',
            'produk_id.integer'  => 'Produk tidak ditemukan.',
        ]);

        try {
            $this->simpanPenjualan($id, (int) $data['produk_id'], (int) $data['qty']);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['qty' => $e->getMessage()]);
        }

        return redirect()->route('penjualan.index')->with('flash', ['success', 'Penjualan disimpan.']);
    }

    public function hapus(Request $request): RedirectResponse
    {
        $id = (int) $request->input('id', 0);

        // Hapus penjualan dan kembalikan stoknya (satu transaksi, baris dikunci)
        DB::transaction(function () use ($id) {
            $lama = Penjualan::lockForUpdate()->find($id);
            if ($lama) {
                Produk::lockForUpdate()->find($lama->produk_id);
                Produk::whereKey($lama->produk_id)->increment('stok', $lama->qty);
                $lama->delete();
            }
        });

        return redirect()->route('penjualan.index')
            ->with('flash', ['success', 'Penjualan dihapus, stok produk dikembalikan.']);
    }

    /**
     * Simpan penjualan (baru kalau $id = 0, edit kalau $id > 0) sekaligus
     * menyesuaikan stok. Semuanya dalam satu transaksi, dan baris yang
     * dipakai dikunci (FOR UPDATE) supaya stok tidak kacau kalau ada
     * dua transaksi berjalan bersamaan.
     */
    private function simpanPenjualan(int $id, int $produkId, int $qty): void
    {
        DB::transaction(function () use ($id, $produkId, $qty) {
            $lama = null;
            if ($id > 0) {
                $lama = Penjualan::lockForUpdate()->find($id)
                    ?? throw new InvalidArgumentException('Data penjualan tidak ditemukan.');
            }

            // kunci produk yang terlibat (urut by id biar tidak deadlock)
            $ids = collect([$produkId, $lama?->produk_id])->filter()->unique()->sort()->values()->all();
            $produk = Produk::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if (! $produk->has($produkId)) {
                throw new InvalidArgumentException('Produk tidak ditemukan.');
            }

            $stok = $produk->map(fn ($p) => (int) $p->stok)->all();
            if ($lama) {
                $stok[(int) $lama->produk_id] += (int) $lama->qty; // kembalikan stok penjualan lama
            }
            if ($qty > $stok[$produkId]) {
                throw new InvalidArgumentException("Stok tidak cukup. Sisa stok: {$stok[$produkId]}");
            }
            $stok[$produkId] -= $qty;

            foreach ($stok as $pid => $sisa) {
                Produk::whereKey($pid)->update(['stok' => $sisa]);
            }

            $harga = (int) $produk[$produkId]->harga;
            $data  = ['produk_id' => $produkId, 'qty' => $qty, 'harga_satuan' => $harga, 'total' => $harga * $qty];

            if ($lama) {
                $lama->update($data);
            } else {
                Penjualan::create($data); // kolom tanggal diisi default database (waktu sekarang)
            }
        });
    }
}
