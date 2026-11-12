<?php
/** @var PDO $pdo */

/**
 * Simpan penjualan (baru kalau $id = 0, edit kalau $id > 0) sekaligus
 * menyesuaikan stok. Semuanya dalam satu transaksi, dan baris yang
 * dipakai dikunci (FOR UPDATE) supaya stok tidak kacau kalau ada
 * dua transaksi berjalan bersamaan.
 */
function simpanPenjualan(PDO $pdo, int $id, int $produkId, int $qty): void
{
    $pdo->beginTransaction();
    try {
        $lama = null;
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT produk_id, qty FROM penjualan WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $lama = $stmt->fetch() ?: throw new InvalidArgumentException('Data penjualan tidak ditemukan.');
        }

        // kunci produk yang terlibat (urut by id biar tidak deadlock)
        $ids = array_values(array_unique(array_filter([$produkId, $lama['produk_id'] ?? null])));
        sort($ids);
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, stok, harga FROM produk WHERE id IN ($in) ORDER BY id FOR UPDATE");
        $stmt->execute($ids);
        $produk = array_column($stmt->fetchAll(), null, 'id');

        if (!isset($produk[$produkId])) {
            throw new InvalidArgumentException('Produk tidak ditemukan.');
        }

        $stok = array_map(fn ($p) => (int) $p['stok'], $produk);
        if ($lama) {
            $stok[(int) $lama['produk_id']] += (int) $lama['qty']; // kembalikan stok penjualan lama
        }
        if ($qty > $stok[$produkId]) {
            throw new InvalidArgumentException("Stok tidak cukup. Sisa stok: {$stok[$produkId]}");
        }
        $stok[$produkId] -= $qty;

        $upd = $pdo->prepare('UPDATE produk SET stok = ? WHERE id = ?');
        foreach ($stok as $pid => $sisa) {
            $upd->execute([$sisa, $pid]);
        }

        $harga = (int) $produk[$produkId]['harga'];
        $data  = [$produkId, $qty, $harga, $harga * $qty];
        if ($id > 0) {
            $pdo->prepare('UPDATE penjualan SET produk_id = ?, qty = ?, harga_satuan = ?, total = ? WHERE id = ?')
                ->execute([...$data, $id]);
        } else {
            $pdo->prepare('INSERT INTO penjualan (produk_id, qty, harga_satuan, total) VALUES (?, ?, ?, ?)')
                ->execute($data);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Hapus penjualan dan kembalikan stoknya
function hapusPenjualan(PDO $pdo, int $id): void
{
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT produk_id, qty FROM penjualan WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        if ($lama = $stmt->fetch()) {
            $pdo->prepare('SELECT id FROM produk WHERE id = ? FOR UPDATE')->execute([$lama['produk_id']]);
            $pdo->prepare('UPDATE produk SET stok = stok + ? WHERE id = ?')->execute([$lama['qty'], $lama['produk_id']]);
            $pdo->prepare('DELETE FROM penjualan WHERE id = ?')->execute([$id]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

$error = null;
$form  = null; // diisi kalau modal harus terbuka lagi (misal validasi gagal)

$produk = $pdo->query('SELECT id, nama, stok, harga FROM produk ORDER BY nama')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($do === 'simpan') {
        $form = [
            'id'        => $id,
            'produk_id' => (int) ($_POST['produk_id'] ?? 0),
            'qty'       => trim((string) ($_POST['qty'] ?? '')),
        ];
        try {
            $qty = filter_var($form['qty'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]);
            if ($qty === false) {
                throw new InvalidArgumentException('Qty harus lebih dari 0.');
            }
            simpanPenjualan($pdo, $id, $form['produk_id'], $qty);
            flash('success', 'Penjualan disimpan.');
            redirect('index.php?page=penjualan');
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        }
        // gagal -> $form tetap terisi, jadi modal terbuka lagi dengan isian tadi
    }

    if ($do === 'hapus') {
        hapusPenjualan($pdo, $id);
        flash('success', 'Penjualan dihapus, stok produk dikembalikan.');
        redirect('index.php?page=penjualan');
    }
}

$daftar = $pdo->query(
    'SELECT pj.id, pj.produk_id, pj.tanggal, pj.qty, pj.total, p.nama AS produk
       FROM penjualan pj JOIN produk p ON p.id = pj.produk_id
      ORDER BY pj.id'
)->fetchAll();

$produkPertama = $produk[0]['id'] ?? '';
?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <h1 class="font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight">Penjualan</h1>
  <button type="button" data-modal="modal-penjualan" data-title="Tambah penjualan"
          <?= $produk ? '' : 'data-alert="Tambahkan produk terlebih dahulu."' ?>
          data-fill="<?= fill_attr(['id' => 0, 'produk_id' => $produkPertama, 'qty' => '']) ?>"
          class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-cobalt text-white">Tambah penjualan</button>
</div>

<div class="bg-white border-2 border-ink rounded-xl shadow-hard overflow-x-auto">
  <table class="w-full text-sm text-left">
    <thead>
      <tr>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">No</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Tanggal</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Produk</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Qty</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Total</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap w-40">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$daftar): ?>
        <tr><td colspan="6" class="px-4 py-10 text-center text-ink/50">Belum ada penjualan. Klik "Tambah penjualan".</td></tr>
      <?php endif; ?>
      <?php foreach ($daftar as $i => $row): ?>
        <tr>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= $i + 1 ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= e(date('d/m/Y', strtotime($row['tanggal']))) ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= e($row['produk']) ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= (int) $row['qty'] ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= e(rupiah($row['total'])) ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap">
            <button type="button" data-modal="modal-penjualan" data-title="Edit penjualan"
                    data-fill="<?= fill_attr(['id' => $row['id'], 'produk_id' => $row['produk_id'], 'qty' => $row['qty']]) ?>"
                    class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none px-3 py-1 text-xs mr-1 bg-sun text-ink">Edit</button>
            <form method="post" action="index.php?page=penjualan" class="inline" onsubmit="return confirm('Yakin hapus penjualan ini? Stok produk akan dikembalikan.')">
              <?= csrf_field() ?>
              <input type="hidden" name="do" value="hapus">
              <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
              <button type="submit" class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none px-3 py-1 text-xs mr-1 bg-coral text-ink">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ================= MODAL PENJUALAN ================= -->
<div id="modal-penjualan" data-modal-box class="fixed inset-0 bg-ink/60 flex items-center justify-center p-4 z-50 <?= $form === null ? 'hidden' : '' ?>">
  <form method="post" action="index.php?page=penjualan" class="bg-white border-2 border-ink rounded-2xl shadow-hard-lg w-full max-w-md p-6 max-h-full overflow-y-auto">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="simpan">
    <input type="hidden" name="id" value="<?= (int) ($form['id'] ?? 0) ?>">
    <h3 data-modal-title class="font-['Bricolage_Grotesque',sans-serif] text-xl font-extrabold mb-4"><?= ($form['id'] ?? 0) ? 'Edit' : 'Tambah' ?> penjualan</h3>

    <?php if ($error): ?>
      <p data-modal-error class="text-sm font-medium bg-coral/40 border-2 border-ink rounded-lg px-3 py-2 mb-4"><?= e($error) ?></p>
    <?php endif; ?>

    <div class="mb-4 flex-1">
      <label class="block text-sm font-semibold mb-1" for="penjualan-produk">Produk</label>
      <select id="penjualan-produk" name="produk_id" required
              class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
        <?php foreach ($produk as $p): ?>
          <option value="<?= (int) $p['id'] ?>" data-harga="<?= (int) $p['harga'] ?>" <?= (int) $p['id'] === (int) ($form['produk_id'] ?? 0) ? 'selected' : '' ?>><?= e($p['nama']) ?> (stok: <?= (int) $p['stok'] ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-4 flex-1">
      <label class="block text-sm font-semibold mb-1" for="penjualan-qty">Qty</label>
      <input id="penjualan-qty" name="qty" type="number" min="1" value="<?= e($form['qty'] ?? '') ?>" required
             class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
    </div>
    <div class="mb-4 flex-1">
      <label class="block text-sm font-semibold mb-1" for="penjualan-total">Total</label>
      <input id="penjualan-total" type="text" readonly
             class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
    </div>
    <div class="flex justify-end gap-3 mt-2">
      <button type="button" data-close-modal class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-white text-ink">Batal</button>
      <button type="submit" class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-cobalt text-white">Simpan</button>
    </div>
  </form>
</div>

<script>
  // hanya tampilan: total dihitung ulang oleh server saat disimpan
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