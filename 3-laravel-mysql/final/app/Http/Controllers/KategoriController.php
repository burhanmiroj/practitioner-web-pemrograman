<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        return view('kategori', ['daftar' => Kategori::orderBy('id')->get()]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $id = (int) $request->input('id', 0);

        $data = $request->validate([
            'nama' => ['required', 'max:100', Rule::unique('kategori', 'nama')->ignore($id ?: null)],
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.max'      => 'Nama kategori maksimal 100 karakter.',
            'nama.unique'   => 'Nama kategori sudah ada.',
        ]);

        if ($id > 0) {
            Kategori::whereKey($id)->update(['nama' => $data['nama']]);
        } else {
            Kategori::create(['nama' => $data['nama']]);
        }

        return redirect()->route('kategori.index')->with('flash', ['success', 'Kategori disimpan.']);
    }

    public function hapus(Request $request): RedirectResponse
    {
        try {
            Kategori::whereKey((int) $request->input('id', 0))->delete();
            $flash = ['success', 'Kategori dihapus.'];
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? 0) !== 1451) { // 1451 = masih dipakai tabel lain
                throw $e;
            }
            $flash = ['error', 'Kategori masih dipakai oleh produk, tidak bisa dihapus.'];
        }

        return redirect()->route('kategori.index')->with('flash', $flash);
    }
}
