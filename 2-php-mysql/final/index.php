<?php
declare(strict_types=1);

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require __DIR__ . '/connection.php';
require __DIR__ . '/helpers.php';

// Kalau belum ada akun sama sekali, buat akun admin dulu (admin / admin123)
if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
    $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)')
        ->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
}

// Belum login -> tampilkan halaman login saja
if (empty($_SESSION['user'])) {
    require __DIR__ . '/pages/login.php';
    exit;
}

// Logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'logout') {
    csrf_check();
    $_SESSION = [];
    session_destroy();
    redirect('index.php');
}

$menu = [
    'dashboard' => 'Dashboard',
    'kategori'  => 'Kategori Produk',
    'produk'    => 'Produk',
    'penjualan' => 'Penjualan',
];
$page = $_GET['page'] ?? 'dashboard';
if (!isset($menu[$page])) {
    $page = 'dashboard';
}

// Isi halaman dibuat dulu (ditampung), supaya file halaman masih bisa redirect
ob_start();
require __DIR__ . "/pages/{$page}.php";
$content = ob_get_clean();
$flash = pull_flash();

$title = 'HCM Stok - Inventory Dashboard';
require __DIR__ . '/partials/head.php';
?>
<body class="bg-paper text-ink font-['Figtree',sans-serif]">
  <div class="flex flex-col min-h-screen">

    <header class="bg-cobalt border-b-2 border-ink sticky top-0 z-40">
      <div class="max-w-6xl mx-auto px-4 md:px-8 py-3 flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
        <div class="flex items-center gap-2 order-1">
          <span class="w-9 h-9 grid place-items-center bg-sun text-ink border-2 border-ink rounded-lg -rotate-6 font-['Bricolage_Grotesque',sans-serif] font-extrabold text-lg">S</span>
          <span class="font-['Bricolage_Grotesque',sans-serif] text-xl font-extrabold text-white tracking-tight">StokKu</span>
        </div>

        <nav class="order-3 md:order-2 w-full md:w-auto flex gap-1 overflow-x-auto md:flex-1">
          <?php foreach ($menu as $key => $label): ?>
            <a href="index.php?page=<?= e($key) ?>"
               class="whitespace-nowrap px-4 py-1.5 rounded-full border-2 text-sm font-semibold cursor-pointer <?= $page === $key
                   ? 'bg-sun text-ink border-ink hover:bg-sun'
                   : 'border-transparent text-white hover:bg-white/15' ?>"><?= e($label) ?></a>
          <?php endforeach; ?>
        </nav>

        <div class="order-2 md:order-3 flex items-center gap-3">
          <p class="hidden sm:block text-sm font-medium text-white">Halo, <?= e($_SESSION['user']['username']) ?></p>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="do" value="logout">
            <button type="submit" class="px-3 py-1 text-xs rounded-lg border-2 border-ink font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-white text-ink">Keluar</button>
          </form>
        </div>
      </div>
    </header>

    <main class="w-full max-w-6xl mx-auto flex-1 p-4 md:p-8">
      <?php if ($flash): ?>
        <p class="text-sm font-medium border-2 border-ink rounded-lg px-3 py-2 mb-4 <?= $flash[0] === 'error' ? 'bg-coral/40' : 'bg-mint/40' ?>"><?= e($flash[1]) ?></p>
      <?php endif; ?>

      <?= $content ?>
    </main>

  </div>

  <script src="modal.js"></script>
</body>
</html>
