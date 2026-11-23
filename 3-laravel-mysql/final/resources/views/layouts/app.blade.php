@include('partials.head', ['title' => 'HCM Stok - Inventory Dashboard'])
@php
    $menu = [
        ['Dashboard', route('dashboard'), request()->routeIs('dashboard')],
        ['Kategori Produk', route('kategori.index'), request()->routeIs('kategori.*')],
        ['Produk', route('produk.index'), request()->routeIs('produk.*')],
        ['Penjualan', route('penjualan.index'), request()->routeIs('penjualan.*')],
    ];
    $flash = session('flash');
@endphp

<body class="bg-paper text-ink font-['Figtree',sans-serif]">
    <div class="flex flex-col min-h-screen">

        <header class="bg-cobalt border-b-2 border-ink sticky top-0 z-40">
            <div
                class="max-w-6xl mx-auto px-4 md:px-8 py-3 flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
                <div class="flex items-center gap-2 order-1">
                    <span
                        class="w-9 h-9 grid place-items-center bg-sun text-ink border-2 border-ink rounded-lg -rotate-6 font-['Bricolage_Grotesque',sans-serif] font-extrabold text-lg">S</span>
                    <span
                        class="font-['Bricolage_Grotesque',sans-serif] text-xl font-extrabold text-white tracking-tight">StokKu</span>
                </div>

                <nav class="order-3 md:order-2 w-full md:w-auto flex gap-1 overflow-x-auto md:flex-1">
                    @foreach ($menu as [$label, $url, $aktif])
                                    <a href="{{ $url }}" class="whitespace-nowrap px-4 py-1.5 rounded-full border-2 text-sm font-semibold cursor-pointer {{ $aktif
                        ? 'bg-sun text-ink border-ink hover:bg-sun'
                        : 'border-transparent text-white hover:bg-white/15' }}">{{ $label }}</a>
                    @endforeach
                </nav>

                <div class="order-2 md:order-3 flex items-center gap-3">
                    <p class="hidden sm:block text-sm font-medium text-white">Halo, {{ auth()->user()->username }}</p>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="px-3 py-1 text-xs rounded-lg border-2 border-ink font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-white text-ink">Keluar</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="w-full max-w-6xl mx-auto flex-1 p-4 md:p-8">
            @if ($flash)
                <p
                    class="text-sm font-medium border-2 border-ink rounded-lg px-3 py-2 mb-4 {{ $flash[0] === 'error' ? 'bg-coral/40' : 'bg-mint/40' }}">
                    {{ $flash[1] }}</p>
            @endif

            @yield('content')
        </main>

    </div>

    <script src="{{ asset('asset/js/modal.js') }}"></script>
</body>

</html>
