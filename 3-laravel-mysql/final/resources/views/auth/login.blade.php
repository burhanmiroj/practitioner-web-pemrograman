@include('partials.head', ['title' => 'Masuk - StokKu'])

<body class="bg-paper text-ink font-['Figtree',sans-serif]">
    <div class="min-h-screen flex items-center justify-center p-4">
        <form method="post" action="{{ route('login') }}"
            class="w-full max-w-sm bg-white border-2 border-ink rounded-2xl shadow-hard-lg p-8">
            @csrf
            <h1 class="font-['Bricolage_Grotesque',sans-serif] text-3xl font-extrabold tracking-tight">StokKu</h1>
            <p class="text-sm text-ink/70 mt-1 mb-6">Masuk untuk mengelola stok dan penjualan.</p>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1" for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="admin" value="{{ old('username') }}"
                    autocomplete="username"
                    class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1" for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="admin123"
                    autocomplete="current-password"
                    class="w-full bg-white border-2 border-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:bg-sun/20 focus:shadow-hard-sm">
            </div>

            @if ($errors->any())
                <p class="text-sm font-medium bg-coral/40 border-2 border-ink rounded-lg px-3 py-2 mb-4">
                    {{ $errors->first() }}</p>
            @endif

            <button type="submit"
                class="w-full px-4 py-2 rounded-lg border-2 border-ink text-sm font-semibold cursor-pointer shadow-hard-sm transition-all hover:-translate-x-px hover:-translate-y-px hover:shadow-hard active:translate-x-0.5 active:translate-y-0.5 active:shadow-none bg-cobalt text-white">Masuk</button>
            <p class="text-xs text-ink/60 text-center mt-4">Akun bawaan: admin / admin123</p>
        </form>
    </div>
</body>

</html>
