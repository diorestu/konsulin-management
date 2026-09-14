<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login : Konsulin Manager</title>
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
    <div class="w-full max-w-xl">
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
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-3">
                    <p class="text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Demo Akun Berdasarkan Role:
                    </p>
                    <span class="text-[11px] text-slate-400">Password default: <code class="text-slate-700 bg-slate-100 px-1 py-0.5 rounded font-mono text-[11px]">password</code></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <!-- Admin Role Card -->
                    <button
                        type="button"
                        id="demo-card-admin"
                        onclick="fillCreds('admin@konsulin.test', 'password', 'demo-card-admin')"
                        class="demo-role-btn text-left p-3 rounded-xl border border-slate-200/90 bg-slate-50/70 hover:bg-slate-50 hover:border-slate-300 transition cursor-pointer flex flex-col justify-between focus:outline-none focus:ring-2 focus:ring-[#0b192c]"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#0b192c] text-white">
                                    Admin
                                </span>
                                <span class="w-2 h-2 rounded-full bg-emerald-500" title="Akun Aktif"></span>
                            </div>
                            <div class="text-xs font-semibold text-slate-900 leading-tight">Dewi Admin</div>
                            <span class="block text-[11px] text-slate-500 font-mono truncate mt-0.5">admin@konsulin.test</span>
                        </div>
                        <div class="mt-2.5 pt-2 border-t border-slate-200/60 text-[10px] text-slate-500 leading-relaxed">
                            Akses penuh workspace, client, staff & website
                        </div>
                    </button>

                    <!-- Reviewer Role Card -->
                    <button
                        type="button"
                        id="demo-card-reviewer"
                        onclick="fillCreds('reviewer@konsulin.test', 'password', 'demo-card-reviewer')"
                        class="demo-role-btn text-left p-3 rounded-xl border border-slate-200/90 bg-slate-50/70 hover:bg-slate-50 hover:border-slate-300 transition cursor-pointer flex flex-col justify-between focus:outline-none focus:ring-2 focus:ring-purple-600"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                    Reviewer
                                </span>
                                <span class="w-2 h-2 rounded-full bg-purple-500" title="Akun Aktif"></span>
                            </div>
                            <div class="text-xs font-semibold text-slate-900 leading-tight">Nadia Reviewer</div>
                            <span class="block text-[11px] text-slate-500 font-mono truncate mt-0.5">reviewer@konsulin.test</span>
                        </div>
                        <div class="mt-2.5 pt-2 border-t border-slate-200/60 text-[10px] text-slate-500 leading-relaxed">
                            Review deliverable, matriks risiko & approval
                        </div>
                    </button>

                    <!-- Staff Role Card -->
                    <button
                        type="button"
                        id="demo-card-staff"
                        onclick="fillCreds('rafi@konsulin.test', 'password', 'demo-card-staff')"
                        class="demo-role-btn text-left p-3 rounded-xl border border-slate-200/90 bg-slate-50/70 hover:bg-slate-50 hover:border-slate-300 transition cursor-pointer flex flex-col justify-between focus:outline-none focus:ring-2 focus:ring-blue-600"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-200">
                                    Staff
                                </span>
                                <span class="w-2 h-2 rounded-full bg-blue-500" title="Akun Aktif"></span>
                            </div>
                            <div class="text-xs font-semibold text-slate-900 leading-tight">Rafi Staff</div>
                            <span class="block text-[11px] text-slate-500 font-mono truncate mt-0.5">rafi@konsulin.test</span>
                        </div>
                        <div class="mt-2.5 pt-2 border-t border-slate-200/60 text-[10px] text-slate-500 leading-relaxed">
                            Pengerjaan task & Always-on-Top time tracker
                        </div>
                    </button>
                </div>

                <!-- Secondary Seeded Accounts (Optional Quick Fill) -->
                <div class="mt-3 pt-2.5 border-t border-dashed border-slate-200/80 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
                    <span class="font-medium text-slate-600">Akun alternatif:</span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button
                            type="button"
                            onclick="fillCreds('boss@konsulin.test', 'password')"
                            class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[10px] transition cursor-pointer"
                            title="Dewi Partner (Admin)"
                        >
                            boss@konsulin.test
                        </button>
                        <button
                            type="button"
                            onclick="fillCreds('nadia@konsulin.test', 'password')"
                            class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[10px] transition cursor-pointer"
                            title="Nadia Consultant (Reviewer)"
                        >
                            nadia@konsulin.test
                        </button>
                        <button
                            type="button"
                            onclick="fillCreds('bagus@konsulin.test', 'password')"
                            class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[10px] transition cursor-pointer"
                            title="Bagus Accountant (Staff)"
                        >
                            bagus@konsulin.test
                        </button>
                    </div>
                </div>
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
        function fillCreds(email, password, cardId) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;

            document.querySelectorAll('.demo-role-btn').forEach(btn => {
                btn.classList.remove('ring-2', 'ring-[#0b192c]', 'border-[#0b192c]', 'bg-white', 'shadow-sm');
                btn.classList.add('border-slate-200/90', 'bg-slate-50/70');
            });

            if (cardId) {
                const activeCard = document.getElementById(cardId);
                if (activeCard) {
                    activeCard.classList.remove('border-slate-200/90', 'bg-slate-50/70');
                    activeCard.classList.add('ring-2', 'ring-[#0b192c]', 'border-[#0b192c]', 'bg-white', 'shadow-sm');
                }
            }
        }
    </script>
</body>
</html>
