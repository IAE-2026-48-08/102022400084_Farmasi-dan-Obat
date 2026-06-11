# Analisis Tugas 3 — Service Farmasi & Obat

**Nama:** Muhammad Fadhlan  
**NIM:** 102022400084  
**Service:** E-Healthcare Farmasi & Obat  
**Mata Kuliah:** BBK2HAB3 - Integrasi Aplikasi Enterprise

---

## 1. Identifikasi Transaksi Kritis

### Transaksi yang Dipilih: **PrescriptionCreated (Pembuatan Resep Digital)**

**Endpoint:** `POST /api/v1/pharmacy`

### Justifikasi Kenapa Ini Transaksi Kritis

Pembuatan resep digital adalah transaksi **state-changing** yang paling kritis dalam Service Farmasi & Obat karena:

1. **Mengubah state sistem secara permanen** — resep yang sudah dibuat dokter tidak bisa dibatalkan sembarangan karena menyangkut keselamatan pasien
2. **Melibatkan data sensitif** — nama obat, dosis, dan frekuensi konsumsi adalah data medis yang harus diaudit
3. **Memiliki efek downstream** — setelah resep dibuat, petugas farmasi harus segera menyiapkan obat (status: PENDING → PREPARING → READY_TO_PICKUP → DISPENSED)
4. **Berpotensi financial impact** — setiap resep yang dibuat akan mempengaruhi stok obat dan biaya pengobatan pasien

---

## 2. Sequence Diagram Interaksi dengan Layanan Terpusat

```
Client          Farmasi Service      SSO Dosen           SOAP Audit          RabbitMQ
  |                   |                  |                    |                   |
  |--POST /pharmacy-->|                  |                    |                   |
  |                   |--POST /auth/token (M2M API Key)------>|                   |
  |                   |<-----------JWT Token------------------|                   |
  |                   |--Save to DB                          |                   |
  |                   |--POST /soap/v1/audit (Bearer JWT)---->|                   |
  |                   |  [TeamID, ActivityName, LogContent]  |                   |
  |                   |<---------ReceiptNumber----------------|                   |
  |                   |--Publish Event (pharmacy.prescription.created)----------->|
  |                   |  [prescription_id, medicine_name, status]               |
  |                   |<---------ACK----------------------------------------------|
  |<--200 Success-----|                  |                    |                   |
  |  [data + integration status]        |                    |                   |
```

### Penjelasan Alur:

1. **Client** mengirim POST request ke endpoint `/api/v1/pharmacy` dengan header `X-IAE-KEY`
2. **Farmasi Service** menyimpan resep ke database SQLite
3. **SSO Login (M2M)** — Service melakukan login ke SSO dosen menggunakan API Key `KEY-MHS-157` untuk mendapatkan JWT token
4. **SOAP Audit** — Menggunakan JWT token, service mengirim audit log dalam format XML ke `/soap/v1/audit` dengan activity name `PrescriptionCreated`
5. **RabbitMQ Publish** — Service mempublish event `pharmacy.prescription.created` ke exchange `iae.central.exchange` agar semua service lain dapat merespons secara asinkron
6. **Response** dikembalikan ke client beserta status integrasi (SSO, SOAP, RabbitMQ)

---

## 3. Implementasi Teknis

### Modul 1: Federated SSO

File: `app/Services/SsoService.php`

- Menggunakan M2M authentication dengan API Key `KEY-MHS-157`
- Endpoint: `POST https://iae-sso.virtualfri.id/api/v1/auth/token`
- JWT token yang didapat digunakan untuk autentikasi SOAP dan RabbitMQ

### Modul 2: SOAP XML Client

File: `app/Services/SoapAuditService.php`

- Mengirim XML Envelope ke `POST https://iae-sso.virtualfri.id/soap/v1/audit`
- Activity name yang diaudit: `PrescriptionCreated`
- Menyimpan `ReceiptNumber` dari response sebagai bukti audit berhasil
- LogContent berisi data resep dalam format JSON (CDATA)

### Modul 3: AMQP Publisher

File: `app/Services/RabbitMqService.php`

- Menggunakan library `php-amqplib/php-amqplib`
- Exchange: `iae.central.exchange`
- Event name: `pharmacy.prescription.created`
- Payload berisi prescription_id, medicine_name, status, dan team_id

---

## 4. Struktur Response POST /api/v1/pharmacy

```json
{
    "status": "success",
    "message": "Resep obat berhasil dicatat",
    "data": {
        "id": "uuid",
        "medicine_name": "Paracetamol 500mg",
        "dosage": "500mg",
        "frequency": "3x sehari",
        "quantity": 10,
        "status": "PENDING"
    },
    "meta": {
        "service_name": "E-Healthcare-Farmasi-dan-Obat",
        "api_version": "v1",
        "integration": {
            "sso": "success",
            "soap": "success",
            "soap_receipt": "IAE-LOG-2026-XXXXXXXX",
            "rabbitmq": "success"
        }
    }
}
```
