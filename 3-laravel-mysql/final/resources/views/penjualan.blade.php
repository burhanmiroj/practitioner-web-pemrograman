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
        $produkPertama = $produk->first()->id ?? '';
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight">Penjualan</h1>
        <button type="button" data-modal="modal-penjualan" data-title="Tambah penjualan" @if ($produk->isEmpty())
        data-alert="Tambahkan produk terlebih dahulu." @endif
            data-fill="{{ $fill(['id' => 0, 'produk_id' => $produkPertama, 'qty' => '']) }}"
            class="{{ $btn }} bg-cobalt text-white">Tambah penjualan</button>
    </div>

    <div class="bg-white border-2 border-ink rounded-xl shadow-hard overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr>
                    <th class="{{ $th }}">No</th>
                    <th class="{{ $th }}">Tanggal</th>
                    <th class="{{ $th }}">Produk</th>
                    <th class="{{ $th }}">Qty</th>
                    <th class="{{ $th }}">Total</th>
                    <th class="{{ $th }} w-40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if ($daftar->isEmpty())
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-ink/50">Belum ada penjualan. Klik "Tambah penjualan".
                        </td>
                    </tr>
                @endif
                @foreach ($daftar as $i => $row)
                    <tr>
                        <td class="{{ $td }}">{{ $i + 1 }}</td>
                        <td class="{{ $td }}">{{ $row->tanggal->format('d/m/Y') }}</td>
                        <td class="{{ $td }}">{{ $row->produk->nama }}</td>
                        <td class="{{ $td }}">{{ (int) $row->qty }}</td>
                        <td class="{{ $td }}">@rupiah($row->total)</td>
                        <td class="{{ $td }}">
                            <button type="button" data-modal="modal-penjualan" data-title="Edit penjualan"
                                data-fill="{{ $fill(['id' => $row->id, 'produk_id' => $row->produk_id, 'qty' => $row->qty]) }}"
                                class="{{ $btnS }} bg-sun text-ink">Edit</button>
                            <form method="post" action="{{ route('penjualan.hapus') }}" class="inline"
                                onsubmit="return confirm('Yakin hapus penjualan ini? Stok produk akan dikembalikan.')">
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

    <!-- ================= MODAL PENJUALAN ================= -->
    <div id="modal-penjualan" data-modal-box
        class="fixed inset-0 bg-ink/60 flex items-center justify-center p-4 z-50 {{ $buka ? '' : 'hidden' }}">
        <form method="post" action="{{ route('penjualan.simpan') }}"
            class="bg-white border-2 border-ink rounded-2xl shadow-hard-lg w-full max-w-md p-6 max-h-full overflow-y-auto">
            @csrf
            <input type="hidden" name="id" value="{{ (int) old('id', 0) }}">
            <h3 data-modal-title class="font-['Bricolage_Grotesque',sans-serif] text-xl font-extrabold mb-4">
                {{ old('id') ? 'Edit' : 'Tambah' }} penjualan</h3>

            @if ($errors->any())
                <p data-modal-error class="text-sm font-medium bg-coral/40 border-2 border-ink rounded-lg px-3 py-2 mb-4">
                    {{ $errors->first() }}</p>
            @endif

            <div class="mb-4 flex-1">
                <label class="block text-sm font-semibold mb-1" for="penjualan-produk">Produk</label>
                <select id="penjualan-produk" name="produk_id" required class="{{ $input }}">
                    @foreach ($produk as $p)
                        <option value="{{ $p->id }}" data-harga="{{ (int) $p->harga }}" @selected((int) $p->id === (int) old('produk_id', 0))>{{ $p->nama }} (stok: {{ (int) $p->stok }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4 flex-1">
                <label class="block text-sm font-semibold mb-1" for="penjualan-qty">Qty</label>
                <input id="penjualan-qty" name="qty" type="number" min="1" value="{{ old('qty') }}" required
                    class="{{ $input }}">
            </div>
            <div class="mb-4 flex-1">
                <label class="block text-sm font-semibold mb-1" for="penjualan-total">Total</label>
                <input id="penjualan-total" type="text" readonly class="{{ $input }}">
            </div>
            <div class="flex justify-end gap-3 mt-2">
                <button type="button" data-close-modal class="{{ $btn }} bg-white text-ink">Batal</button>
                <button type="submit" class="{{ $btn }} bg-cobalt text-white">Simpan</button>
            </div>
        </form>
    </div>

    <script>
        const pilihProduk = document.getElementById('penjualan-produk');
        const inputQty = document.getElementById('penjualan-qty');
        const inputTotal = document.getElementById('penjualan-total');
        const hitungTotal = () => {
            const harga = Number(pilihProduk.selectedOptions[0]?.dataset.harga || 0);
            const qty = Number(inputQty.value);
            inputTotal.value = harga && qty > 0 ? 'Rp ' + (harga * qty).toLocaleString('id-ID') : '';
        };
        pilihProduk.addEventListener('change', hitungTotal);
        inputQty.addEventListener('input', hitungTotal);
        hitungTotal();
    </script>
@endsection
