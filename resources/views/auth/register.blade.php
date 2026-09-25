<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun : Konsulin Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 selection:bg-[#1e3e62] selection:text-white">
    <div class="w-full max-w-md">
        <!-- Logo & Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-[#0b192c] text-white shadow-md mb-3">
                <x-heroicon-o-squares-2x2 class="w-7 h-7" />
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Konsulin Manager</h1>
            <p class="text-sm text-slate-500 mt-1">Registrasi Anggota Tim Konsultan</p>
        </div>

        <!-- Auth Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-7 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-900">Buat Akun Baru</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftarkan akun anggota tim untuk mengelola proyek.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                    <div class="flex items-center gap-2 font-semibold mb-1">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 text-rose-600" />
                        <span>Mohon perbaiki isian form:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <x-heroicon-o-user class="w-4 h-4" />
                        </div>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            placeholder="Dewi Sartika"
                            class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/50 border border-slate-200 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0b192c] focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Email Kantor
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <x-heroicon-o-envelope class="w-4 h-4" />
                        </div>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="nama@konsulin.id"
                            class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/50 border border-slate-200 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0b192c] focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi (Min. 8 Karakter)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <x-heroicon-o-lock-closed class="w-4 h-4" />
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/50 border border-slate-200 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0b192c] focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Konfirmasi Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <x-heroicon-o-shield-check class="w-4 h-4" />
                        </div>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            placeholder="••••••••"
                            class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/50 border border-slate-200 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0b192c] focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 px-4 bg-[#0b192c] hover:bg-[#1e3e62] text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer mt-2"
                >
                    <span>Daftarkan Akun</span>
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                </button>
            </form>
        </div>

        <div class="text-center mt-6">
            <p class="text-xs text-slate-500">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-semibold text-[#0b192c] hover:underline">
                    Masuk di sini
                </a>
            </p>
        </div>
    </div>
</body>
</html>
