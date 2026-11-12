<?php
/** @var PDO $pdo */
$stat = $pdo->query(
    'SELECT
        (SELECT COUNT(*) FROM kategori)                AS kategori,
        (SELECT COUNT(*) FROM produk)                  AS produk,
        (SELECT COALESCE(SUM(stok), 0) FROM produk)    AS stok,
        (SELECT COALESCE(SUM(total), 0) FROM penjualan) AS pendapatan'
)->fetch();

$cards = [
    ['Kategori',         number_format((int) $stat['kategori'], 0, ',', '.'), 'bg-sun'],
    ['Produk',           number_format((int) $stat['produk'], 0, ',', '.'),   'bg-sky'],
    ['Total stok',       number_format((int) $stat['stok'], 0, ',', '.'),     'bg-mint'],
    ['Total pendapatan', rupiah($stat['pendapatan']),                         'bg-coral'],
];
?>
<h1 class="font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight mb-5">Ringkasan toko</h1>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
  <?php foreach ($cards as [$label, $value, $warna]): ?>
    <div class="<?= $warna ?> border-2 border-ink rounded-xl shadow-hard p-5">
      <p class="text-sm font-semibold"><?= e($label) ?></p>
      <p class="font-['Bricolage_Grotesque',sans-serif] text-4xl font-extrabold mt-2 break-words"><?= e($value) ?></p>
    </div>
  <?php endforeach; ?>
</div>
