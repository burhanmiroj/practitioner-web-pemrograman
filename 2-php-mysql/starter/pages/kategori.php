<?php
/** @var PDO $pdo */
$error = null;
$form  = null; // diisi kalau modal harus terbuka lagi (misal validasi gagal)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($do === 'simpan') {
        $nama = trim((string) ($_POST['nama'] ?? ''));
        try {
            if ($nama === '') {
                throw new InvalidArgumentException('Nama kategori wajib diisi.');
            }
            if (panjang($nama) > 100) {
                throw new InvalidArgumentException('Nama kategori maksimal 100 karakter.');
            }
            if ($id > 0) {
                $pdo->prepare('UPDATE kategori SET nama = ? WHERE id = ?')->execute([$nama, $id]);
            } else {
                $pdo->prepare('INSERT INTO kategori (nama) VALUES (?)')->execute([$nama]);
            }
            flash('success', 'Kategori disimpan.');
            redirect('index.php?page=kategori');
        } catch (InvalidArgumentException $e) {
            $error = $e->getMessage();
        } catch (PDOException $e) {
            if (db_error_code($e) !== 1062) throw $e;
            $error = 'Nama kategori sudah ada.';
        }
        $form = ['id' => $id, 'nama' => $nama]; // buka lagi modalnya dengan isian tadi
    }

    if ($do === 'hapus') {
        try {
            $pdo->prepare('DELETE FROM kategori WHERE id = ?')->execute([$id]);
            flash('success', 'Kategori dihapus.');
        } catch (PDOException $e) {
            if (db_error_code($e) !== 1451) throw $e;
            flash('error', 'Kategori masih dipakai oleh produk, tidak bisa dihapus.');
        }
        redirect('index.php?page=kategori');
    }
}

$daftar = $pdo->query('SELECT id, nama FROM kategori ORDER BY id')->fetchAll();
?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <h1 class="font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight">Kategori Produk</h1>
  <button type="button" data-modal="modal-kategori" data-title="Tambah kategori" data-fill="<?= fill_attr(['id' => 0, 'nama' => '']) ?>"
          class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-cobalt text-white">Tambah kategori</button>
</div>

<div class="bg-white border-2 border-ink rounded-xl shadow-hard overflow-x-auto">
  <table class="w-full text-sm text-left">
    <thead>
      <tr>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">No</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap">Nama kategori</th>
        <th class="px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap w-40">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$daftar): ?>
        <tr><td colspan="3" class="px-4 py-10 text-center text-ink/50">Belum ada kategori. Klik "Tambah kategori".</td></tr>
      <?php endif; ?>
      <?php foreach ($daftar as $i => $row): ?>
        <tr>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= $i + 1 ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap"><?= e($row['nama']) ?></td>
          <td class="px-4 py-3 border-b border-ink/10 whitespace-nowrap">
            <button type="button" data-modal="modal-kategori" data-title="Edit kategori" data-fill="<?= fill_attr(['id' => $row['id'], 'nama' => $row['nama']]) ?>"
                    class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none px-3 py-1 text-xs mr-1 bg-sun text-ink">Edit</button>
            <form method="post" action="index.php?page=kategori" class="inline" onsubmit="return confirm('Yakin hapus kategori ini?')">
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

<!-- ================= MODAL KATEGORI ================= -->
<div id="modal-kategori" data-modal-box class="fixed inset-0 bg-ink/60 flex items-center justify-center p-4 z-50 <?= $form === null ? 'hidden' : '' ?>">
  <form method="post" action="index.php?page=kategori" class="bg-white border-2 border-ink rounded-2xl shadow-hard-lg w-full max-w-md p-6 max-h-full overflow-y-auto">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="simpan">
    <input type="hidden" name="id" value="<?= (int) ($form['id'] ?? 0) ?>">
    <h3 data-modal-title class="font-['Bricolage_Grotesque',sans-serif] text-xl font-extrabold mb-4"><?= ($form['id'] ?? 0) ? 'Edit' : 'Tambah' ?> kategori</h3>

    <?php if ($error): ?>
      <p data-modal-error class="text-sm font-medium bg-coral/40 border-2 border-ink rounded-lg px-3 py-2 mb-4"><?= e($error) ?></p>
    <?php endif; ?>

    <div class="mb-4 flex-1">
      <label class="block text-sm font-semibold mb-1" for="kategori-nama">Nama kategori</label>
      <input id="kategori-nama" name="nama" type="text" maxlength="100" value="<?= e($form['nama'] ?? '') ?>" required
             class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
    </div>
    <div class="flex justify-end gap-3 mt-2">
      <button type="button" data-close-modal class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-white text-ink">Batal</button>
      <button type="submit" class="px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-cobalt text-white">Simpan</button>
    </div>
  </form>
</div>