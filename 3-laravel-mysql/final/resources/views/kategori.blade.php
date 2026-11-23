@extends('layouts.app')

@section('content')
    @php
        $btn = 'px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none';
        $btnS = $btn . ' px-3 py-1 text-xs mr-1';
        $input = 'w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm';
        $th = 'px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap';
        $td = 'px-4 py-3 border-b border-ink/10 whitespace-nowrap';
        $fill = fn(array $d) => json_encode($d, JSON_UNESCAPED_UNICODE);
        $buka = $errors->any(); // modal terbuka lagi kalau validasi gagal
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight">Kategori Produk</h1>
        <button type="button" data-modal="modal-kategori" data-title="Tambah kategori"
            data-fill="{{ $fill(['id' => 0, 'nama' => '']) }}" class="{{ $btn }} bg-cobalt text-white">Tambah
            kategori</button>
    </div>

    <div class="bg-white border-2 border-ink rounded-xl shadow-hard overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr>
                    <th class="{{ $th }}">No</th>
                    <th class="{{ $th }}">Nama kategori</th>
                    <th class="{{ $th }} w-40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if ($daftar->isEmpty())
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-ink/50">Belum ada kategori. Klik "Tambah kategori".
                        </td>
                    </tr>
                @endif
                @foreach ($daftar as $i => $row)
                    <tr>
                        <td class="{{ $td }}">{{ $i + 1 }}</td>
                        <td class="{{ $td }}">{{ $row->nama }}</td>
                        <td class="{{ $td }}">
                            <button type="button" data-modal="modal-kategori" data-title="Edit kategori"
                                data-fill="{{ $fill(['id' => $row->id, 'nama' => $row->nama]) }}"
                                class="{{ $btnS }} bg-sun text-ink">Edit</button>
                            <form method="post" action="{{ route('kategori.hapus') }}" class="inline"
                                onsubmit="return confirm('Yakin hapus kategori ini?')">
                                @csrf
                                <input type="hidden" name="id" value="{{ $row->id }}">
                                <button type="submit" class="{{ $btnS }} bg-coral text-ink">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ================= MODAL KATEGORI ================= -->
    <div id="modal-kategori" data-modal-box
        class="fixed inset-0 bg-ink/60 flex items-center justify-center p-4 z-50 {{ $buka ? '' : 'hidden' }}">
        <form method="post" action="{{ route('kategori.simpan') }}"
            class="bg-white border-2 border-ink rounded-2xl shadow-hard-lg w-full max-w-md p-6 max-h-full overflow-y-auto">
            @csrf
            <input type="hidden" name="id" value="{{ (int) old('id', 0) }}">
            <h3 data-modal-title class="font-['Bricolage_Grotesque',sans-serif] text-xl font-extrabold mb-4">
                {{ old('id') ? 'Edit' : 'Tambah' }} kategori</h3>

            @if ($errors->any())
                <p data-modal-error class="text-sm font-medium bg-coral/40 border-2 border-ink rounded-lg px-3 py-2 mb-4">
                    {{ $errors->first() }}</p>
            @endif

            <div class="mb-4 flex-1">
                <label class="block text-sm font-semibold mb-1" for="kategori-nama">Nama kategori</label>
                <input id="kategori-nama" name="nama" type="text" maxlength="100" value="{{ old('nama') }}" required
                    class="{{ $input }}">
            </div>
            <div class="flex justify-end gap-3 mt-2">
                <button type="button" data-close-modal class="{{ $btn }} bg-white text-ink">Batal</button>
                <button type="submit" class="{{ $btn }} bg-cobalt text-white">Simpan</button>
            </div>
        </form>
    </div>
@endsection
