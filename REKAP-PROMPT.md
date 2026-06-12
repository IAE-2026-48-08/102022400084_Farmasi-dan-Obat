# Rekap Log Prompting dengan AI

**Nama:** Muhammad Fadhlan  
**NIM:** 102022400084  
**Mata Kuliah:** BBK2HAB3 - Integrasi Aplikasi Enterprise  
**Service:** E-Healthcare Farmasi & Obat  

---

## Penggunaan AI dalam Pengerjaan Tugas

Saya menggunakan Claude (Anthropic) sebagai asisten selama mengerjakan Tugas 2 dan Tugas 3. Penggunaan AI lebih ke arah troubleshooting error dan generate boilerplate, bukan untuk memahami konsep dasarnya.

---

## Tugas 2 — Build Your Service

### Setup Awal
Pertama-tama saya minta bantuan AI untuk setup project Laravel karena project yang saya gunakan adalah template dari teman kelompok (repo `102022400056_rent-contract`). Saya perlu adaptasi strukturnya untuk service Farmasi & Obat saya sendiri.

AI membantu saya:
- Upgrade PHP dari 8.2 ke 8.5 karena project butuh PHP ^8.3
- Aktifkan extension `openssl`, `fileinfo`, `zip`, `pdo_sqlite` di `php.ini`
- Setup migration, model, controller, request, dan resource untuk tabel `pharmacy`

**Prompt yang saya gunakan:**
> "gw mau bikin service farmasi dan obat untuk e-healthcare, buatkan seluruh codingannya"

---

### Masalah GraphQL

Ini bagian yang paling bikin pusing. Awalnya GraphQL error karena schema masih import `patient.graphql` dan `appointment.graphql` yang sudah saya hapus setelah memutuskan fokus ke service Farmasi & Obat saja.

Errornya:
```
Did not find GraphQL schema import at graphql/models/patient.graphql
```

Saya tanya ke AI cara fixnya, dan solusinya adalah update `graphql/schema.graphql` agar hanya import `pharmacy.graphql`. Setelah itu GraphQL berjalan normal dan query `pharmacies` berhasil return data.

**Prompt:**
> "graphql error karena masih ada import patient dan appointment yang udah dihapus, cara fixnya gmn?"

---

### Swagger Dark Theme
Karena tampilan default Swagger terlalu plain, saya minta AI untuk buatkan custom dark theme. AI membuat custom blade view dengan CSS yang cukup detail untuk override styling bawaan Swagger UI.

---

## Tugas 3 — Integrasi Cloud Dosen

### SSO dan SOAP
Untuk SSO dan SOAP relatif tidak ada masalah besar. AI membantu saya membuat `SsoService.php` untuk login M2M dan `SoapAuditService.php` untuk kirim SOAP Envelope ke cloud dosen. Keduanya langsung berhasil di percobaan pertama setelah saya sesuaikan `TeamID` dari `TEAM-08` ke `TEAM-13` (sesuai info dari response SSO).

### Masalah RabbitMQ

Ini yang paling lama diselesaikan. Awalnya saya coba koneksi langsung ke RabbitMQ menggunakan `php-amqplib` dengan host dan port AMQP, tapi selalu gagal dengan error:

```
ACCESS_REFUSED - Login was refused using authentication mechanism AMQPLAIN
```

Saya sempat coba pakai token warga (`warga29@ktp.iae.id`) tapi malah dapat error 403 di SOAP juga. Setelah beberapa kali trial and error dengan bantuan AI dan cek log Laravel, akhirnya ketemu bahwa RabbitMQ di cloud dosen tidak pakai koneksi AMQP langsung tapi lewat HTTP API di endpoint `/api/v1/messages/publish` dengan Bearer token M2M (bukan token warga).

Setelah diganti ke HTTP API dengan token M2M, RabbitMQ langsung berhasil.

**Prompt:**
> "rabbitmq masih failed, cek log errornya"

Dari log ditemukan:
```
"Forbidden: M2M Bearer token required"
```

Itulah kuncinya — harus pakai M2M token, bukan token warga.

---

## Hasil Akhir

Setiap kali `POST /api/v1/pharmacy` dipanggil, sistem otomatis menjalankan:
1. Login SSO M2M → dapat JWT
2. Kirim SOAP Audit → dapat ReceiptNumber
3. Publish event ke RabbitMQ

Response:
```json
"integration": {
    "sso": "success",
    "soap": "success",
    "rabbitmq": "success",
    "soap_receipt": "IAE-LOG-2026-54DAC4D3"
}
```

---

**Muhammad Fadhlan — 102022400084**
