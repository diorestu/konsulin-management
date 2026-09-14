# Konsulin Manager Always-on-Top Floating Desktop Timer

Aplikasi desktop *always-on-top* untuk macOS dan Windows yang terhubung langsung dengan Konsulin Manager.

## Fitur Utama

1. **Always-On-Top Floating Screen**:
   - Berada di lapisan teratas layar monitor (di atas Excel, Word, browser, atau aplikasi tax software lainnya).
   - Menampilkan task aktif, nama klien, dan digital clock timer (HH:MM:SS) yang menghitung durasi pengerjaan secara real-time.
2. **Compact vs Expanded Mode**:
   - Tombol toggle `⇕` memungkinkan window menciut menjadi mini pill bar (320x56) yang sangat ringkas dan tidak menghalangi pekerjaan.
3. **Deep Linking Protocol (`konsulin://`)**:
   - Klik "Always on top" atau "Buka Desktop" di web Konsulin Manager untuk langsung membuka desktop app dan memulai tracking task terkait secara otomatis.
4. **Sinkronisasi 2-Arah dengan Web**:
   - Jika waktu kerja dimulai atau dihentikan di web, desktop app akan memperbarui tampilannya secara instan.
   - Jika dihentikan dari desktop app, web otomatis merekam durasi log dan menandai progres.

## Cara Menjalankan (Development Mode)

```bash
cd desktop
npm install
npm start
```

## Cara Build Installer (macOS & Windows)

### 1. Build untuk macOS (.dmg dan .app)
```bash
cd desktop
npm run build:mac
```
Hasil installer `.dmg` akan berada di dalam folder `desktop/dist/`.

### 2. Build untuk Windows (.exe / installer NSIS)
```bash
cd desktop
npm run build:win
```
Hasil installer `.exe` akan berada di dalam folder `desktop/dist/`.

## Alternatif Tanpa Instalasi (Native Web Picture-in-Picture)

Bagi pengguna yang belum menginstal aplikasi desktop, Konsulin Manager juga dilengkapi dengan fitur **Web Document Picture-in-Picture**:
- Klik tombol **"Always-on-Top"** pada floating widget di pojok kanan bawah web Konsulin Manager (didukung di Chrome dan Edge).
- Browser akan memunculkan jendela floating native OS Always-On-Top langsung dari web tanpa perlu mengunduh atau menginstal file tambahan.
