Tema: Clean & Putih — kesan bersih, tenang, profesional, dan dipercaya.

## 1. Filosofi Desain

Klinik Pratama Sehat Bersama mengusung gaya minimalis dengan dominasi warna putih untuk menciptakan kesan higienis, tenang, dan modern. Elemen visual dijaga sederhana agar pasien—termasuk lansia dan pengguna awam teknologi—merasa nyaman dan tidak kebingungan saat mengakses informasi maupun layanan.

**Prinsip utama:**
- Bersih & lapang (banyak whitespace)
- Menenangkan, bukan mengintimidasi
- Terpercaya & profesional
- Mudah dibaca, aksesibel untuk semua usia

## Tech Stack Frontend
- **CSS Framework**: **Tailwind CSS** (Utility-first styling, responsive, custom palette `#2FA791`)
- **JavaScript Framework**: **Alpine.js** (Interaktivitas ringan: Modal Janji Temu, Mobile Navbar Menu, Dropdown, Chat AI Scheduling)

## 2. Palet Warna

| Warna | Kode Hex | Fungsi |
|---|---|---|
| Putih (Base) | `#FFFFFF` | Latar utama |
| Off-White / Abu sangat muda | `#F7F9FA` | Latar sekunder, card background |
| Hijau Toska (Primary) | `#2FA791` | Warna identitas — tombol, ikon, aksen utama (simbol kesehatan & ketenangan) |
| Hijau Toska Muda | `#E4F5F1` | Highlight lembut, badge, hover state |
| Biru Muda (Secondary) | `#4A90E2` | Aksen sekunder — link, info medis |
| Abu Gelap (Teks Utama) | `#2E2E2E` | Teks judul & isi |
| Abu Netral (Teks Sekunder) | `#7A7A7A` | Keterangan, caption, placeholder |
| Merah Lembut (Alert) | `#E4574C` | Peringatan, status darurat |
| Hijau Sukses | `#3FB27F` | Notifikasi berhasil, status "tersedia" |


## 3. Tipografi

- Font Judul: Poppins (SemiBold / Bold) — modern, ramah, mudah dibaca
- Font Isi/Body: Inter atau Nunito Sans (Regular/Medium) — jelas di berbagai ukuran layar

| Elemen | Ukuran | Bobot |
|---|---|---|
| H1 (Judul Halaman) | 32–40px | Bold |
| H2 (Sub Judul) | 24–28px | SemiBold |
| H3 (Judul Kartu) | 18–20px | SemiBold |
| Body Text | 14–16px | Regular |
| Caption/Label | 12–13px | Medium |

Line-height disarankan 1.5–1.6 untuk keterbacaan optimal.


## 4. Layout & Tata Ruang

- Grid 12 kolom (desktop), 4 kolom (mobile)
- Whitespace lega antar-section (min. 64px desktop, 32px mobile)
- Card dengan sudut membulat (`border-radius: 12–16px`) dan bayangan tipis (`box-shadow` halus, tidak tajam)
- Navigasi atas sederhana: Logo — Menu (Beranda, Layanan, Dokter, Jadwal, Kontak) — Tombol "Buat Janji"
- Struktur halaman utama:
  1. Hero section (headline + CTA "Buat Janji Temu")
  2. Layanan unggulan (grid card ikon)
  3. Profil dokter/tenaga medis
  4. Jadwal praktik & lokasi (peta)
  5. Testimoni pasien
  6. Footer (kontak, jam operasional, media sosial)


## 5. Komponen UI

**Tombol (Button)**
- Primary: latar hijau toska `#2FA791`, teks putih, radius 8px, padding 12–20px
- Secondary/outline: border hijau toska, teks hijau toska, latar putih
- Hover: sedikit lebih gelap + transisi halus (200ms)

**Kartu (Card)**
- Latar putih/off-white, shadow halus, radius 12px
- Ikon berwarna hijau toska di bagian atas
- Judul tegas + deskripsi singkat abu-abu

**Ikon**
- Gaya outline/line-icon, konsisten, warna hijau toska atau abu gelap
- Ukuran seragam (24px atau 32px)

**Form (mis. pendaftaran/janji temu)**
- Input field: border tipis abu muda, radius 8px, latar putih
- Focus state: border hijau toska + shadow lembut
- Label jelas di atas input, bukan placeholder saja


## 6. Logo & Branding

- Logo memuat simbol kesehatan (mis. palang/daun/denyut nadi) dipadukan warna hijau toska
- Nama "Sehat Bersama" ditulis dengan font Poppins SemiBold
- Versi logo: penuh warna (untuk latar putih) dan monokrom putih (untuk latar warna/foto)


## 7. Aksesibilitas

- Kontras teks terhadap latar minimal rasio 4.5:1 (WCAG AA)
- Ukuran font dasar minimal 14px, dapat diperbesar
- Tombol dan elemen interaktif memiliki area sentuh minimal 44x44px
- Hindari hanya mengandalkan warna untuk menyampaikan informasi penting (tambahkan ikon/teks)

## 8. Mood/Kesan yang Ingin Dibangun

Bersih • Tenang • Terpercaya • Ramah • Modern tanpa terkesan dingin