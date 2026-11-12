// Dimuat SETELAH tailwind-config.js. Menyuntik CSS @apply ke Tailwind CDN (tanpa build).
const css = `

@layer components {
  .hide { @apply !hidden; }
  .body-page { @apply bg-paper text-ink font-['Figtree',sans-serif]; }

  /* ---------- Login ---------- */
  .login-page { @apply min-h-screen flex items-center justify-center p-4; }
  .login-box { @apply w-full max-w-sm bg-white border-2 border-ink rounded-2xl shadow-hard-lg p-8; }
  .login-title { @apply font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight; }
  .login-subtitle { @apply text-sm text-ink/70 mt-1 mb-6; }
  .login-error { @apply text-sm font-medium bg-coral/40 border-2 border-ink rounded-lg px-3 py-2 mb-4; }
  .login-hint { @apply text-xs text-ink/60 text-center mt-4; }

  /* ---------- Form ---------- */
  .form-group { @apply mb-4 flex-1; }
  .form-row { @apply flex gap-3; }
  .form-label { @apply block text-sm font-semibold mb-1; }
  .form-input { @apply w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm; }

  /* ---------- Tombol ---------- */
  .btn { @apply px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none; }
  .btn-full { @apply w-full; }
  .btn-primary { @apply bg-cobalt text-white; }
  .btn-light { @apply bg-white text-ink; }
  .btn-small { @apply px-3 py-1 text-xs mr-1; }
  .btn-edit { @apply bg-sun text-ink; }
  .btn-delete { @apply bg-coral text-ink; }

  /* ---------- Navbar atas ---------- */
  .app-wrapper { @apply flex flex-col min-h-screen; }
  .navbar { @apply bg-cobalt border-b-2 border-ink sticky top-0 z-40; }
  .navbar-inner { @apply max-w-6xl mx-auto px-4 md:px-8 py-3 flex flex-wrap items-center justify-between gap-x-6 gap-y-3; }
  .brand { @apply flex items-center gap-2 order-1; }
  .brand-mark { @apply w-9 h-9 grid place-items-center bg-sun text-ink border-2 border-ink rounded-lg -rotate-6 font-['Bricolage_Grotesque',sans-serif] font-extrabold text-lg; }
  .brand-name { @apply font-['Bricolage_Grotesque',sans-serif] text-xl font-extrabold text-white tracking-tight; }
  .nav-menu { @apply order-3 md:order-2 w-full md:w-auto flex gap-1 overflow-x-auto md:flex-1; }
  .menu-item { @apply whitespace-nowrap px-4 py-1.5 rounded-full border-2 border-transparent text-sm font-semibold text-white cursor-pointer hover:bg-white/15; }
  .menu-active { @apply bg-sun text-ink border-ink hover:bg-sun; }
  .nav-user { @apply order-2 md:order-3 flex items-center gap-3; }
  .nav-username { @apply hidden sm:block text-sm font-medium text-white; }

  /* ---------- Halaman ---------- */
  .main { @apply w-full max-w-6xl mx-auto flex-1 p-4 md:p-8; }
  .page-header { @apply flex flex-wrap items-center justify-between gap-3 mb-5; }
  .page-title { @apply font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight mb-5; }
  .page-header .page-title { @apply mb-0; }
  .card-grid { @apply grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5; }
  .stat-card { @apply bg-white border-2 border-ink rounded-xl shadow-hard p-5; }
  .stat-card:nth-child(1) { @apply bg-sun; }
  .stat-card:nth-child(2) { @apply bg-sky; }
  .stat-card:nth-child(3) { @apply bg-mint; }
  .stat-card:nth-child(4) { @apply bg-coral; }
  .stat-label { @apply text-sm font-semibold; }
  .stat-value { @apply font-['Bricolage_Grotesque',sans-serif] text-4xl font-extrabold mt-2 break-words; }

  /* ---------- Tabel ---------- */
  .table-box { @apply bg-white border-2 border-ink rounded-xl shadow-hard overflow-x-auto; }
  .table { @apply w-full text-sm text-left; }
  .th { @apply px-4 py-3 bg-ink text-white font-semibold whitespace-nowrap; }
  .th-action { @apply w-40; }
  .td { @apply px-4 py-3 border-b border-ink/10 whitespace-nowrap; }
  .td-empty { @apply px-4 py-10 text-center text-ink/50; }
  .product-img { @apply w-12 h-12 object-cover rounded-lg border-2 border-ink bg-sky; }

  /* ---------- Modal ---------- */
  .modal { @apply fixed inset-0 bg-ink/60 flex items-center justify-center p-4 z-50; }
  .modal-box { @apply bg-white border-2 border-ink rounded-2xl shadow-hard-lg w-full max-w-md p-6 max-h-full overflow-y-auto; }
  .modal-title { @apply font-['Bricolage_Grotesque',sans-serif] text-xl font-extrabold mb-4; }
  .modal-actions { @apply flex justify-end gap-3 mt-2; }
}
`;
const el = document.createElement('style');
el.type = 'text/tailwindcss';
el.textContent = css;
document.head.appendChild(el);