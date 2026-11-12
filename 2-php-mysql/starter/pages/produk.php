<?php
/** @var PDO $pdo */
$error = null;
$form  = null; // diisi kalau modal harus terbuka lagi (misal validasi gagal)

$kategori = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($do === 'simpan') {
        $form = [
            'id'          => $id,
            'nama'        => trim((string) ($_POST['nama'] ?? '')),
            'kategori_id' => (int) ($_POST['kategori_id'] ?? 0),
            'gambar'      => trim((string) ($_POST['gambar'] ?? '')),
            'stok'        => trim((string) ($_POST['stok'] ?? '')),
            'harga'       => trim((string) ($_POST['harga'] ?? '')),
        ];
        try {
            if ($form['nama'] === '' || $form['stok'] === '' || $form['harga'] === '') {
                throw new InvalidArgumentException('Nama, stok, dan harga wajib diisi.');
            }
            if (panjang($form['nama']) > 150) {
                throw new InvalidArgumentException('Nama produk maksimal 150 karakter.');
            }
            $opsi  = ['options' => ['min_range' => 0, 'max_range' => 2147483647]];
            $stok  = filter_var($form['stok'], FILTER_VALIDATE_INT, $opsi);
            $harga = filter_var($form['harga'], FILTER_VALIDATE_INT, $opsi);
            if ($stok === false || $harga === false) {
                throw new InvalidArgumentException('Stok dan harga harus berupa angka 0 atau lebih.');
            }
            $gambar = $form['gambar'];
            if ($gambar !== '') {
                if (panjang($gambar) > 500 || !preg_match('#^https?://#i', $gambar) || !filter_var($gambar, FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException('URL gambar harus diawali http:// atau https://');
                }
            }

            $data = [$form['kategori_id'], $form['nama'], $gambar === '' ? null : $gambar, $stok, $harga];
            if ($id > 0) {
                $pdo->prepare('UPDATE produk SET kategori_id = ?, nama = ?, gambar = ?, stok = ?, harga = ? WHERE id = ?')
                    ->execute([...$data, $id]);
            } else {
                $pdo->prepare('INSERT INTO produk (kategori_id, nama, gambar, stok, harga) VALUES (?, ?, ?, ?, ?)')
                    ->execute($data);
            }
            flash('success', 'Produk disimpan.');
            redirect('index.php?page=produk');
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (PDOException $e) {
            if (db_error_code($e) !== 1452) throw $e;
            $error = 'Kategori tidak valid.';
        }
        // gagal -> $form tetap terisi, jadi modal terbuka lagi dengan isian tadi
    }

    if ($do === 'hapus') {
        try {
            $pdo->prepare('DELETE FROM produk WHERE id = ?')->execute([$id]);
            flash('success', 'Produk dihapus.');
        } catch (PDOException $e) {
            if (db_error_code($e) !== 1451) throw $e;
            flash('error', 'Produk sudah ada di data penjualan, tidak bisa dihapus.');
        }
        redirect('index.php?page=produk');
    }
}

$daftar = $pdo->query(
    'SELECT p.id, p.nama, p.gambar, p.kategori_id, p.stok, p.harga, k.nama AS kategori
       FROM produk p JOIN kategori k ON k.id = p.kategori_id
      ORDER BY p.id'
)->fetchAll();

$kategoriPertama = $kategori[0]['id'] ?? '';
?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <h1 class="font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight">Produk</h1>
  <button type="button" data-modal="modal-produk" data-title="Tambah produk"
          <?= $kategori ? '' : 'data-alert="Tambahkan kategori terlebih dahulu."' ?>
          data-fill="<?= fill_attr(['id' => 0, 'nama' => '', 'kategori_id' => $kategoriPertama, 'gambar' => '', 'stok' => '', 'harga' => '']) ?>"
          class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-cobalt text-white">Tambah produk</button>
</div>

<div class="bg-white border-2 border-ink rounded-xl shadow-hard overflow-x-auto">
  <table class="w-full text-sm text-left">
    <thead>
      <tr>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">No</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Gambar</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Nama</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Kategori</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Stok</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Harga</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap w-40">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$daftar): ?>
        <tr><td colspan="7" class="px-4 py-10 text-center text-ink/50">Belum ada produk. Klik "Tambah produk".</td></tr>
      <?php endif; ?>
      <?php foreach ($daftar as $i => $row): ?>
        <tr>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= $i + 1 ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap">
            <?php if ($row['gambar']): ?>
              <img src="<?= e($row['gambar']) ?>" alt="<?= e($row['nama']) ?>" class="w-12 h-12 object-cover rounded-lg border-2 border-ink bg-sky">
            <?php else: ?>
              <div class="w-12 h-12 rounded-lg border-2 border-ink bg-sky"></div>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= e($row['nama']) ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= e($row['kategori']) ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= (int) $row['stok'] ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= e(rupiah($row['harga'])) ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap">
            <button type="button" data-modal="modal-produk" data-title="Edit produk"
                    data-fill="<?= fill_attr(['id' => $row['id'], 'nama' => $row['nama'], 'kategori_id' => $row['kategori_id'], 'gambar' => $row['gambar'] ?? '', 'stok' => $row['stok'], 'harga' => $row['harga']]) ?>"
                    class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none px-3 py-1 text-xs mr-1 bg-sun text-ink">Edit</button>
            <form method="post" action="index.php?page=produk" class="inline" onsubmit="return confirm('Yakin hapus produk ini?')">
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

<!-- ================= MODAL PRODUK ================= -->
<div id="modal-produk" data-modal-box class="fixed inset-0 bg-ink/60 flex items-center justify-center p-4 z-50 <?= $form === null ? 'hidden' : '' ?>">
  <form method="post" action="index.php?page=produk" class="bg-white border-2 border-ink rounded-2xl shadow-hard-lg w-full max-w-md p-6 max-h-full overflow-y-auto">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="simpan">
    <input type="hidden" name="id" value="<?= (int) ($form['id'] ?? 0) ?>">
    <h3 data-modal-title class="font-['Bricolage_Grotesque',sans-serif] text-xl font-extrabold mb-4"><?= ($form['id'] ?? 0) ? 'Edit' : 'Tambah' ?> produk</h3>

    <?php if ($error): ?>
      <p data-modal-error class="text-sm font-medium bg-coral/40 border-2 border-ink rounded-lg px-3 py-2 mb-4"><?= e($error) ?></p>
    <?php endif; ?>

    <div class="mb-4 flex-1">
      <label class="block text-sm font-semibold mb-1" for="produk-nama">Nama produk</label>
      <input id="produk-nama" name="nama" type="text" maxlength="150" value="<?= e($form['nama'] ?? '') ?>" required
             class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
    </div>
    <div class="mb-4 flex-1">
      <label class="block text-sm font-semibold mb-1" for="produk-kategori">Kategori</label>
      <select id="produk-kategori" name="kategori_id" required
              class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
        <?php foreach ($kategori as $k): ?>
          <option value="<?= (int) $k['id'] ?>" <?= (int) $k['id'] === (int) ($form['kategori_id'] ?? 0) ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-4 flex-1">
      <label class="block text-sm font-semibold mb-1" for="produk-gambar">URL gambar</label>
      <input id="produk-gambar" name="gambar" type="text" maxlength="500" placeholder="https://..." value="<?= e($form['gambar'] ?? '') ?>"
             class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
    </div>
    <div class="flex gap-3">
      <div class="mb-4 flex-1">
        <label class="block text-sm font-semibold mb-1" for="produk-stok">Stok</label>
        <input id="produk-stok" name="stok" type="number" min="0" value="<?= e($form['stok'] ?? '') ?>" required
               class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
      </div>
      <div class="mb-4 flex-1">
        <label class="block text-sm font-semibold mb-1" for="produk-harga">Harga (Rp)</label>
        <input id="produk-harga" name="harga" type="number" min="0" value="<?= e($form['harga'] ?? '') ?>" required
               class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
      </div>
    </div>
    <div class="flex justify-end gap-3 mt-2">
      <button type="button" data-close-modal class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-white text-ink">Batal</button>
      <button type="submit" class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-cobalt text-white">Simpan</button>
    </div>
  </form>
</div>