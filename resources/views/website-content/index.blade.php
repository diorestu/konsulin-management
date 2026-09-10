<x-layouts.app title="Website Content - Konsulin Manager">
    <div class="page-heading">
        <div>
            <p class="eyebrow">Public website CMS</p>
            <h1>Website Content</h1>
            <p class="muted">Kelola konten website konsulin.ID tanpa mengubah data aplikasi internal.</p>
        </div>
        <a class="button secondary" href="{{ route('home') }}" target="_blank" rel="noopener">Preview Website</a>
    </div>

    <div class="stack">
        @forelse ($contents as $content)
            <section class="card">
                <div class="card-head">
                    <div>
                        <h2>{{ $content->label }}</h2>
                        <p class="muted">Key: {{ $content->key }}</p>
                    </div>
                    <span class="label {{ $content->is_published ? 'success' : '' }}">{{ $content->is_published ? 'Published' : 'Draft' }}</span>
                </div>
                <form method="POST" action="{{ route('website-content.update', $content) }}" class="form-grid">
                    @csrf
                    @method('PUT')
                    <label>Judul
                        <input name="title" value="{{ old('title', $content->title) }}" maxlength="255">
                    </label>
                    <label class="wide">Isi konten
                        <textarea name="body" rows="4" maxlength="10000">{{ old('body', $content->body) }}</textarea>
                    </label>
                    <label>Teks tombol
                        <input name="button_text" value="{{ old('button_text', $content->button_text) }}" maxlength="80">
                    </label>
                    <label>URL tombol
                        <input name="button_url" value="{{ old('button_url', $content->button_url) }}" maxlength="255">
                    </label>
                    <label class="checkbox-row wide"><input type="checkbox" name="is_published" value="1" @checked($content->is_published)> Tampilkan di website publik</label>
                    <div class="form-actions wide"><button class="button" type="submit">Save Content</button></div>
                </form>
            </section>
        @empty
            <div class="card"><p class="muted">Belum ada konten website. Jalankan seeder untuk membuat konten awal.</p></div>
        @endforelse
    </div>
</x-layouts.app>
