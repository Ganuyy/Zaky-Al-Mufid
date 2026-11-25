# Prompt Motion Video — SipOrder (SO)

> Dokumen ini berisi paket lengkap produksi video motion/promosi aplikasi **SipOrder**:
> konsep, identitas visual, storyboard, naskah voice-over, teks di layar, prompt AI
> siap-tempel per adegan, musik & SFX, serta spesifikasi teknis.
> **Durasi total: 55 detik (di bawah 1 menit).**

---

## 1. Ringkasan Video

| Item | Keterangan |
|---|---|
| **Judul** | "SipOrder — Pesanan Rapi, Pelanggan Happy" |
| **Produk** | SipOrder (SO) — aplikasi pemesanan minuman (POS) berbasis web desktop |
| **Target audiens** | Pemilik kafe/kedai minuman, manajer outlet, kasir & barista |
| **Tujuan** | Memperkenalkan alur end-to-end: pesan → bayar → antrean barista → laporan |
| **Gaya** | Motion graphics modern, bersih, hangat, premium-but-friendly |
| **Durasi** | 55 detik |
| **Format utama** | 1920×1080 (16:9, landscape) 30fps |
| **Format sekunder** | 1080×1920 (9:16, Reels/Shorts/TikTok) 30fps |
| **Bahasa** | Indonesia (VO + teks layar) |
| **Tone** | Energetik, hangat, meyakinkan, tidak berlebihan |

---

## 2. Identitas Visual (Brand Guide untuk Video)

Gunakan palet & tipografi yang sama dengan aplikasi agar konsisten.

**Palet warna**

| Nama | Hex | Penggunaan |
|---|---|---|
| Brown (utama) | `#713B1C` | Logo, judul, elemen brand |
| Brown Dark | `#542A16` | Background gelap, teks tebal |
| Caramel / Brown Light | `#A66A38` | Aksen, highlight, garis |
| Cream | `#F8EBDD` | Background lembut |
| Cream Light | `#FFFCF7` | Background utama UI |
| Green | `#279B4C` | Status "Lunas", "Selesai", CTA sukses |
| Green Light | `#2FB75C` | Highlight tombol |
| Text | `#3B2A1F` | Teks utama |
| Muted | `#8A7565` | Teks sekunder |

**Tipografi**
- Utama: **Poppins** (Semibold/Bold untuk judul, Regular untuk body).
- Angka/harga: Poppins Medium dengan warna brown.

**Gaya visual**
- Sudut membulat (radius ~14px), kartu dengan shadow lembut (`0 4px 18px rgba(84,42,22,.09)`).
- Ikon garis (Bootstrap Icons style): cup-hot, person, lock, cart, credit-card, printer, bar-chart.
- Motion: *ease-out* halus, *spring* ringan pada kartu/tombol, tanpa gerakan agresif.
- Tekstur latar: gradasi cream hangat + partikel uap kopi tipis (subtle) untuk nuansa kedai.

---

## 3. Struktur Naratif (Beat Sheet)

```
[0–5s]   HOOK        — Logo reveal + pertanyaan pemantik
[5–11s]  PROBLEM     — Antrean & pesanan kacau saat jam sibuk
[11–17s] SOLUTION    — SipOrder hadir: satu dashboard
[17–26s] POS         — Pilih menu, customisasi, keranjang
[26–34s] PAYMENT     — Bayar & cetak struk thermal
[34–41s] BARISTA     — Antrean terkelola, status jelas
[41–48s] REPORT      — Laporan penjualan real-time
[48–55s] CLOSING     — Logo + tagline + CTA
```

---

## 4. Storyboard Lengkap

> Kolom **Prompt AI** dirancang agar bisa ditempel ke tool image/video generator
> (Midjourney, Runway, Kling, Sora, Pika, dll). Untuk hasil paling akurat, gunakan
> **screenshot asli aplikasi** sebagai *image reference* lalu terapkan gerakan kamera.

### SCENE 1 — HOOK (0:00–0:05) · 5 detik

- **Visual:** Layar cream hangat. Logo SipOrder muncul dari tengah dengan efek *scale-up + settle*, disertai cincin cahaya caramel yang meluas. Uap kopi tipis mengambang di background.
- **Motion / Kamera:** Slow push-in; logo pop dengan spring; partikel uap mengalir naik.
- **Teks layar:** `SipOrder` (muncul) → `Pesanan rapi. Tanpa ribet.`
- **Voice-over:** “Pesanan menumpuk saat jam sibuk?”
- **SFX:** *whoosh* lembut + *soft chime*.
- **Transisi:** Whip-pan ke kanan (cepat, 0.3s).

> **Prompt AI:** Motion graphic intro, warm cream background (#FFFCF7), SipOrder logo center-screen, soft caramel glow ring expanding, subtle floating coffee steam particles, smooth spring scale-up reveal, cinematic soft shadows, premium minimal branding, 1920x1080, 30fps, 5 seconds.

---

### SCENE 2 — PROBLEM (0:05–0:11) · 6 detik

- **Visual:** Ilustrasi/mockup kafe sibuk: nota kertas berserakan, tulisan tangan tidak terbaca, kasir panik, pelanggan menunggu. Warna sedikit desaturasi (dingin) untuk kontras dengan hangatnya SipOrder.
- **Motion / Kamera:** Handheld feel ringan, guncangan halus; tumpukan nota berjatuhan; jam dinding berdetak cepat.
- **Teks layar:** `Nota berantakan?` → `Antrean makin panjang?`
- **Voice-over:** “Catatan berantakan, antrean panjang, dan laporan yang bikin pusing?”
- **SFX:** Murmur kafe + *clock ticking* dipercepat + *paper rustle*.
- **Transisi:** Glitch-to-clean → *flash white* ke UI bersih (0.4s).

> **Prompt AI:** Busy cafe counter during rush hour, messy handwritten order notes scattered, blurred barista working fast, slightly desaturated cool tones, shallow depth of field, documentary style, dynamic handheld camera, subtle motion blur, 1920x1080, 30fps, 6 seconds.

---

### SCENE 3 — SOLUTION (0:11–0:17) · 6 detik

- **Visual:** Layar "bersih" muncul dengan UI SipOrder (login/dashboard). Kartu-kartu fitur (Kelola Pesanan, Pembayaran & Struk, Antrean Barista) mengambang dan menyusun diri.
- **Motion / Kamera:** Kamera orbit ringan pada mockup desktop; kartu fitur slide-in berurutan dengan spring; UI di-render tajam di atas background cream.
- **Teks layar:** `Kenalan dengan SipOrder` → 3 chip: `Kelola Pesanan · Pembayaran & Struk · Antrean Barista`
- **Voice-over:** “Kenalan dengan SipOrder — semua pesanan, dalam satu layar.”
- **SFX:** *Pop* tiap kartu + *uplifting riser* singkat.
- **Transisi:** Push-through into layar POS (0.3s).

> **Prompt AI:** Clean desktop UI mockup of a beverage POS app on a laptop screen, warm cream and brown palette, floating feature cards assembling into place, soft depth of field, elegant product showcase, subtle camera orbit, studio lighting, 1920x1080, 30fps, 6 seconds.

---

### SCENE 4 — POS / PESAN (0:17–0:26) · 9 detik

- **Visual:** Layar POS asli: grid kartu menu (kopi, teh, dll.), filter kategori, kolom cari. Kursor "virtual" memilih menu → masuk keranjang → buka opsi customisasi (gula, suhu, level es, catatan) → qty bertambah → subtotal terupdate.
- **Motion / Kamera:** Screen-recording dipercepat 1.5×; zoom fokus ke kartu menu lalu ke panel keranjang; angka total beranimasi *count-up*.
- **Teks layar:** `Pilih menu` → `Custom: gula · suhu · es` → `Total otomatis`
- **Voice-over:** “Pilih menu, atur gula, suhu, dan level es. Keranjang dan total terhitung otomatis.”
- **SFX:** *Tap* klik halus, *ding* saat item masuk keranjang, *tick* count-up.
- **Transisi:** Swipe-left ke layar checkout/pembayaran (0.3s).

> **Prompt AI:** Screen recording of a POS beverage ordering interface, grid of drink menu cards, cursor clicking add-to-cart, cart panel sliding in, customization chips (sugar, temperature, ice level), animated total counting up, warm cream UI, crisp 1080p, smooth UI micro-interactions, 9 seconds.

---

### SCENE 5 — PEMBAYARAN & STRUK (0:26–0:34) · 8 detik

- **Visual:** Layar checkout: pilih metode (Tunai, QRIS, Kartu Debit, E-Wallet), input nama pelanggan, tombol “Konfirmasi Pembayaran”. Lalu struk thermal 58mm keluar dari printer dengan animasi gulung, struk sedikit terangkat.
- **Motion / Kamera:** Close-up ke opsi pembayaran; sukses checkmark hijau berdenyut; cut ke printer, struk keluar realistis dengan bayangan.
- **Teks layar:** `Tunai · QRIS · Debit · E-Wallet` → `Struk thermal otomatis`
- **Voice-over:** “Bayar dengan tunai, QRIS, debit, atau e-wallet. Struk thermal langsung tercetak.”
- **SFX:** *Kaching* ringan + *printer whirr* + *ding* sukses.
- **Transisi:** Cut cepat ke antrean barista (0.2s).

> **Prompt AI:** Close-up of POS checkout screen with payment options (cash, QRIS, debit, e-wallet), green success checkmark pulsing, then a thermal receipt printing out from a small receipt printer and lifting up, warm cafe lighting, shallow depth of field, macro detail on paper, 1920x1080, 30fps, 8 seconds.

---

### SCENE 6 — ANTREAN BARISTA (0:34–0:41) · 7 detik

- **Visual:** Layar antrean barista: kartu pesanan lunas dengan nama pelanggan & catatan customisasi jelas. Status berpindah: `Dibayar` → `Diproses` → `Siap Diambil` → `Selesai` (chip warna bergerak).
- **Motion / Kamera:** Kartu antrean bergeser; status progress bar mengisi; checkmark hijau menyala. Zoom ringan mengikuti kartu dari kiri ke kanan.
- **Teks layar:** `Dibayar → Diproses → Siap → Selesai`
- **Voice-over:** “Barista lihat antrean lunas, proses, tandai siap — pelanggan tahu kapan pesanan jadi.”
- **SFX:** *Swoosh* tiap perpindahan status + *chime* selesai.
- **Transisi:** Slide-up ke dashboard laporan (0.3s).

> **Prompt AI:** Barista order-queue dashboard on a tablet/screen, order cards with customer names and notes, status chips transitioning from Paid to Processing to Ready to Completed, green progress indicators animating, warm cream and brown UI, smooth UI motion, 1920x1080, 30fps, 7 seconds.

---

### SCENE 7 — LAPORAN (0:41–0:48) · 7 detik

- **Visual:** Dashboard admin: grafik penjualan naik, kartu ringkasan (total transaksi, pendapatan, menu terlaris), tombol Export CSV.
- **Motion / Kamera:** Grafik batang/gambar tumbuh dari bawah; angka ringkasan *count-up*; garis tren ditarik dari kiri ke kanan.
- **Teks layar:** `Laporan real-time` → `Top menu · Pendapatan · Export CSV`
- **Voice-over:** “Pantau penjualan, menu terlaris, dan ekspor laporan — semua real-time.”
- **SFX:** *Data tick* lembut + *positive riser*.
- **Transisi:** Zoom-out ke logo & tagline (0.4s).

> **Prompt AI:** Modern admin analytics dashboard on desktop, sales bar and line charts animating upward, KPI summary cards with counting-up numbers, warm cream and brown palette, clean data visualization, subtle camera push-in, studio lighting, 1920x1080, 30fps, 7 seconds.

---

### SCENE 8 — CLOSING / CTA (0:48–0:55) · 7 detik

- **Visual:** Layar cream hangat, logo SipOrder horizontal. Di bawah: tagline & CTA. Tombol hijau “Coba Sekarang” berdenyut lembut. Uap kopi tipis mengambang.
- **Motion / Kamera:** Logo settle di tengah; tagline fade-in; tombol CTA pop dengan glow; partikel naik pelan; slow zoom-out penutup.
- **Teks layar:** `SipOrder (SO)` → `Pesanan rapi, pelanggan happy.` → `Coba SipOrder hari ini`
- **Voice-over:** “SipOrder. Pesanan rapi, pelanggan happy. Coba sekarang.”
- **SFX:** *Chime* penutup + *soft whoosh*.
- **Transisi:** Fade to logo lockup → cut to black (0.5s).

> **Prompt AI:** Elegant motion logo outro, SipOrder horizontal wordmark centered on warm cream background, tagline fading in below, green CTA button pulsing with soft glow, floating coffee steam particles, premium minimal end-card, slow zoom-out, 1920x1080, 30fps, 7 seconds.

---

## 5. Naskah Voice-Over (Lengkap, ~55 detik)

> Gaya baca: hangat, percaya diri, tempo sedang (≈ 2,3 kata/detik).
> Total ± 95 kata → pas untuk 50–55 detik dengan jeda napas.

| Waktu | Naskah |
|---|---|
| 0:00–0:05 | “Pesanan menumpuk saat jam sibuk?” |
| 0:05–0:11 | “Catatan berantakan, antrean panjang, dan laporan yang bikin pusing?” |
| 0:11–0:17 | “Kenalan dengan SipOrder — semua pesanan, dalam satu layar.” |
| 0:17–0:26 | “Pilih menu, atur gula, suhu, dan level es. Keranjang dan total terhitung otomatis.” |
| 0:26–0:34 | “Bayar dengan tunai, QRIS, debit, atau e-wallet. Struk thermal langsung tercetak.” |
| 0:34–0:41 | “Barista lihat antrean lunas, proses, tandai siap — pelanggan tahu kapan pesanan jadi.” |
| 0:41–0:48 | “Pantau penjualan, menu terlaris, dan ekspor laporan — semua real-time.” |
| 0:48–0:55 | “SipOrder. Pesanan rapi, pelanggan happy. Coba sekarang.” |

---

## 6. Teks di Layar (On-Screen Text) — Ringkas

| Waktu | Teks Utama | Sub-teks / Chip |
|---|---|---|
| 0:00–0:05 | SipOrder | Pesanan rapi. Tanpa ribet. |
| 0:05–0:11 | Nota berantakan? | Antrean makin panjang? |
| 0:11–0:17 | Kenalan dengan SipOrder | Kelola Pesanan · Pembayaran & Struk · Antrean Barista |
| 0:17–0:26 | Pilih menu | Gula · Suhu · Es · Total otomatis |
| 0:26–0:34 | Pembayaran fleksibel | Tunai · QRIS · Debit · E-Wallet · Struk thermal |
| 0:34–0:41 | Antrean terkelola | Dibayar → Diproses → Siap → Selesai |
| 0:41–0:48 | Laporan real-time | Top menu · Pendapatan · Export CSV |
| 0:48–0:55 | SipOrder | Pesanan rapi, pelanggan happy. · Coba SipOrder |

---

## 7. Musik, SFX & Suara

- **Musik:** Lo-fi / acoustic upbeat, hangat, 95–105 BPM, tanpa vokal. Naik energi di SCENE 4–7, lalu *resolve* lembut di SCENE 8.
- **Referensi mood:** “warm lo-fi corporate”, “feel-good cafe promo”, “soft percussive tech”.
- **SFX wajib:** whoosh transisi, soft chime logo, tap klik UI, kaching pembayaran, printer whirr, data tick, positive riser closing.
- **Mix:** VO di depan (−3 dB), musik di bawah (−15 s/d −18 dB), SFX punchy namun tidak menutupi VO. Ducking musik saat VO berbicara.

---

## 8. Spesifikasi Teknis Ekspor

| Parameter | Nilai |
|---|---|
| Resolusi utama | 1920×1080 (16:9) |
| Resolusi vertikal | 1080×1920 (9:16) |
| Frame rate | 30 fps |
| Codec | H.264 (MP4), bitrate 10–12 Mbps |
| Audio | AAC 192 kbps, stereo, −14 LUFS (standar sosial media) |
| Safe area | Jaga teks 10% dari tepi (untuk UI platform) |
| Subtitle | Burn-in Bahasa Indonesia, ukuran ≥ 32px (1080p), kontras tinggi |

---

## 9. Prompt AI Siap-Tempel (Versi Utuh, Bahasa Inggris)

> Tempel prompt ini ke generator video (Runway / Kling / Sora / Pika) untuk
> menghasilkan satu klip promo kontinu. Gunakan screenshot aplikasi sebagai
> *image-to-video reference* agar UI akurat.

```
Create a 55-second premium motion-graphics promo video for "SipOrder", a modern
beverage POS desktop/web application for cafes. Style: clean, warm, friendly-premium.
Color palette: warm cream (#FFFCF7), coffee brown (#713B1C), caramel accents (#A66A38),
fresh green (#279B4C). Typography: Poppins. Mood: cozy cafe meets sleek tech.

Sequence:
1) 0-5s Hook: warm cream background, SipOrder logo spring-reveals with caramel glow ring
   and floating coffee steam particles, text "Pesanan rapi. Tanpa ribet."
2) 5-11s Problem: busy cafe rush, messy handwritten order notes, blurred fast barista,
   cool desaturated tones, handheld feel, clock ticking.
3) 11-17s Solution: clean UI mockup on laptop, floating feature cards assembling with
   spring motion, soft camera orbit.
4) 17-26s POS: screen recording of menu grid, cursor selecting drink, customization
   chips (sugar, temperature, ice), cart sliding in, animated total count-up.
5) 26-34s Payment: checkout screen with cash/QRIS/debit/e-wallet, green success check,
   then thermal receipt printing and lifting from a small printer.
6) 34-41s Barista: order queue dashboard, status chips transitioning Paid -> Processing ->
   Ready -> Completed with green progress.
7) 41-48s Reports: admin analytics, bar & line charts animating up, KPI numbers counting,
   Export CSV button.
8) 48-55s Closing: SipOrder logo end-card on cream, tagline "Pesanan rapi, pelanggan happy.",
   pulsing green CTA button, steam particles, slow zoom-out.

Camera: smooth push-ins, gentle orbits, subtle springs, no harsh motion. Lighting: soft,
warm, studio. Add kinetic Indonesian subtitles. Sound: warm upbeat lo-fi, UI clicks,
kaching, printer whirr, soft chimes. Output 1920x1080, 30fps, 55 seconds, cinematic UI polish.
```

---

## 10. Catatan Produksi (Checklist)

- [ ] Ambil screenshot/recording asli: login, POS, checkout, struk, antrean barista, laporan.
- [ ] Samakan warna UI dengan palet di Bagian 2 (jangan recolor sembarangan).
- [ ] Pastikan teks nama pelanggan & catatan customisasi terlihat pada scene barista.
- [ ] Gunakan angka contoh realistis, mis. total `Rp 48.000` (format Rupiah dari aplikasi).
- [ ] Verifikasi durasi tiap scene ≤ target; total ≤ 55 detik.
- [ ] Review subtitle: ejaan, ukuran, aman dari *safe area*.
- [ ] Ekspor versi 16:9 dan 9:16; cek ulang di layar ponsel.
- [ ] Loudness final −14 LUFS, tanpa clipping.

---

© 2026 SipOrder (SO) — Paket Prompt Motion Video
