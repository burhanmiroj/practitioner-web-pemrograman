<?php
declare(strict_types=1);

// Tampilkan teks dengan aman di HTML
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// 15000 -> Rp 15.000
function rupiah(int|float|string $angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// ---------- CSRF: semua form POST wajib membawa token ----------
function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Sesi tidak valid. Silakan muat ulang halaman.');
    }
}

// ---------- Pesan sekali tampil (setelah redirect) ----------
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [$type, $message];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// Kode error MySQL: 1062 = data kembar, 1451 = masih dipakai tabel lain,
// 1452 = referensi tidak ada
function db_error_code(PDOException $e): int
{
    return (int) ($e->errorInfo[1] ?? 0);
}

// Jumlah karakter (bukan byte), tanpa butuh ekstensi mbstring
function panjang(string $teks): int
{
    return (int) preg_match_all('/./su', $teks);
}

// Data yang akan diisikan ke form modal (dibaca JavaScript lewat atribut data-fill)
function fill_attr(array $data): string
{
    return e(json_encode($data, JSON_UNESCAPED_UNICODE));
}