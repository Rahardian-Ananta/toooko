# Duitku PHP Prototype — Jualan Item/Gambar (Sandbox)

Prototipe web sederhana untuk jualan item/gambar dengan payment gateway **Duitku (POP/Create Invoice)**.

## Fitur
- Listing produk digital (contoh: paket gambar).
- Checkout → membuat invoice ke Duitku → redirect ke halaman pembayaran.
- **Callback** (server-to-server) untuk update status pembayaran (verifikasi `signature` MD5 sesuai doks).
- **Return URL** untuk menampilkan status ke pembeli dan tombol **Download** setelah pembayaran sukses.
- Penyimpanan order sederhana pakai file JSON (`storage/orders.json`) agar cepat dicoba.
- Token download sekali pakai.

> Catatan: Proyek ini untuk **sandbox/testing**. Untuk produksi:
> - Pindahkan `DUITKU_ENV=production` dan pastikan domain Anda **HTTPS** serta **publicly accessible** untuk menerima callback.
> - Ganti penyimpanan dari JSON ke database (MySQL/PostgreSQL).
> - Tambahkan autentikasi admin, validasi input lebih ketat, logging, dsb.

## Cara Menjalankan (Windows/Mac/Linux)
1. Pastikan ada PHP 8+.
2. Ekstrak zip ini, buka terminal di folder proyek.
3. Salin `.env.example` jadi `.env` lalu isi kredensial **Duitku Sandbox** Anda (Merchant Code & API Key).
4. Jalankan server dev:
   ```bash
   php -S localhost:8000 -t public
   ```
5. Buka `http://localhost:8000`.
6. Setelah checkout dan dibawa ke halaman Duitku Sandbox, selesaikan pembayaran uji (lihat **Testing** di doks). Anda akan diarahkan balik ke `/api/return.php`. Status akhir akan ter-update setelah **callback** diterima.

> Agar callback jalan saat lokal dev, gunakan tunneling (mis: **ngrok**):
> ```bash
> ngrok http http://localhost:8000
> ```
> Lalu set `DUITKU_CALLBACK_URL` ke URL ngrok: `https://xxxxxxxx.ngrok.io/api/callback.php`

## Struktur
```
public/
  index.html
api/
  create_invoice.php
  callback.php
  return.php
  order_status.php
  download.php
config.php
products.json
storage/
  orders.json
  tokens.json
assets/
  img/ (gambar preview)
  digital/ (file digital / contoh zip)
.env.example
```

## Dokumen Rujukan
- Create Invoice (POP): https://docs.duitku.com/pop/en/ (Endpoint, headers, payload, response).
- Header Signature **request**: `SHA256(merchantCode + timestamp(ms, WIB) + apiKey)`.
- **Callback** body signature verifikasi: `MD5(merchantCode + amount + merchantOrderId + apiKey)`.
- Redirect URL parameters: `merchantOrderId`, `reference`, `resultCode`.

## Lisensi
Kode ini dibuat untuk tujuan demo/edukasi.
