<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $contents['hero']->title ?? 'Konsulin — Pendamping Keputusan Bisnis' }}</title>
    <meta name="description" content="{{ $contents['hero']->body ?? 'Konsultasi pajak, akuntansi, dan finansial untuk bisnis yang lebih siap.' }}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <main class="public-site">
        <nav class="public-nav"><strong>KONSULIN.ID</strong><div><a href="#services">Layanan</a><a href="#contact">Kontak</a><a class="button" href="{{ $contents['hero']->button_url ?? '#contact' }}">{{ $contents['hero']->button_text ?? 'Mulai Konsultasi' }}</a></div></nav>
        <section class="public-hero">
            <p class="eyebrow">Tax · Accounting · Advisory</p>
            <h1>{{ $contents['hero']->title ?? 'Konsultasi yang membuat bisnis lebih siap.' }}</h1>
            <p>{{ $contents['hero']->body ?? 'Konsulin membantu bisnis menata pajak, akuntansi, dan keputusan finansial dengan pendampingan yang jelas.' }}</p>
            <a class="button" href="{{ $contents['hero']->button_url ?? '#contact' }}">{{ $contents['hero']->button_text ?? 'Mulai Konsultasi' }}</a>
        </section>
        <section id="services" class="public-section"><p class="eyebrow">Our approach</p><h2>{{ $contents['services']->title ?? 'Keahlian yang bekerja untuk Anda.' }}</h2><p>{{ $contents['services']->body ?? 'Dari kepatuhan pajak sampai laporan keuangan, pilih dukungan yang sesuai dengan tahap bisnis Anda.' }}</p><div class="service-grid"><article><b>Tax</b><span>Kepatuhan yang lebih tenang.</span></article><article><b>Accounting</b><span>Laporan yang siap dipakai.</span></article><article><b>Advisory</b><span>Keputusan dengan konteks.</span></article></div></section>
        <section id="contact" class="public-cta"><h2>{{ $contents['contact']->title ?? 'Siap membicarakan kebutuhan bisnis Anda?' }}</h2><p>{{ $contents['contact']->body ?? 'Ceritakan tantangan Anda dan tim Konsulin akan membantu menentukan langkah berikutnya.' }}</p><a class="button light" href="{{ $contents['contact']->button_url ?? 'mailto:hello@konsulin.id' }}">{{ $contents['contact']->button_text ?? 'Hubungi Konsulin' }}</a></section>
    </main>
</body>
</html>
