<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Konsulin Manager</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #f8fafc;
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
            <p class="text-sm text-slate-500 mt-1">Jira-style Consulting & Project Management</p>
        </div>

        <!-- Auth Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-7 sm:p-8">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-900">Masuk ke Workspace</h2>
                <p class="text-xs text-slate-500 mt-0.5">Masukkan kredensial Anda untuk melanjutkan.</p>
            </div>

            @if (session('status'))
                <div class="mb-5 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                    <x-heroicon-o-check-circle class="w-4 h-4 shrink-0 text-emerald-600" />
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium">
                    <div class="flex items-center gap-2 font-semibold mb-1">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 shrink-0 text-rose-600" />
                        <span>Autentikasi gagal</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

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
                            autofocus
                            placeholder="nama@konsulin.id"
                            class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/50 border border-slate-200 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0b192c] focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Kata Sandi
                        </label>
                    </div>
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

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600 select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-[#0b192c] focus:ring-[#0b192c]">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 px-4 bg-[#0b192c] hover:bg-[#1e3e62] text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer mt-2"
                >
                    <span>Masuk ke Akun</span>
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                </button>
            </form>

            <!-- Quick Demo Credentials for Fast Testing -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2.5">
                    Demo Akun Uji Coba:
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        onclick="fillCreds('boss@konsulin.test', 'password')"
                        class="p-2.5 text-left rounded-lg border border-slate-200 hover:border-slate-300 bg-slate-50/70 hover:bg-slate-50 transition cursor-pointer"
                    >
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-semibold text-slate-900">Partner (Boss)</span>
                        </div>
                        <span class="block text-[11px] text-slate-500 truncate mt-0.5">boss@konsulin.test</span>
                    </button>
                    <button
                        type="button"
                        onclick="fillCreds('nadia@konsulin.test', 'password')"
                        class="p-2.5 text-left rounded-lg border border-slate-200 hover:border-slate-300 bg-slate-50/70 hover:bg-slate-50 transition cursor-pointer"
                    >
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-semibold text-slate-900">Consultant</span>
                        </div>
                        <span class="block text-[11px] text-slate-500 truncate mt-0.5">nadia@konsulin.test</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 text-center mt-2">Password default: <code>password</code></p>
            </div>
        </div>

        <div class="text-center mt-6">
            <p class="text-xs text-slate-500">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-[#0b192c] hover:underline">
                    Daftar Akun Baru
                </a>
            </p>
        </div>
    </div>

    <script>
        function fillCreds(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
