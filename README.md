# E-Healthcare — Service Melakukan Rawat Jalan

Bagian dari ekosistem **E-Healthcare**, service ini menangani seluruh alur proses bisnis rawat jalan — mulai dari pasien datang, konsultasi dengan dokter, hingga pengambilan obat di farmasi. Dibangun di atas Laravel dengan REST API dan GraphQL sebagai protokol komunikasi antar service.

## Fitur

- **REST API** — Endpoint terstruktur untuk 3 service: Data Pasien, Jadwal Dokter, dan Farmasi & Obat
- **GraphQL** — Query fleksibel dengan Lighthouse, cocok untuk integrasi lintas service
- **Swagger UI** — Dokumentasi API interaktif, lengkap dengan autentikasi API Key
- **GraphiQL** — Playground untuk eksplorasi skema GraphQL secara langsung

## Stack

| | |
|---|---|
| Framework | [Laravel](https://laravel.com/) |
| REST Docs | [L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger) + [swagger-php](https://github.com/zircote/swagger-php) |
| GraphQL Server | [Lighthouse](https://lighthouse-php.com/) |
| GraphQL UI | [Laravel GraphiQL](https://github.com/mll-lab/laravel-graphiql) |
| Database | SQLite |

## Prasyarat

- PHP >= 8.3
- Composer
- Node.js & NPM

## Cara Menjalankan

```bash
# 1. Clone repo
git clone https://github.com/IAE-2026-48-08/102022400084_Farmasi-dan-Obat.git
cd 102022400084_Farmasi-dan-Obat

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi
php artisan migrate

# 5. Jalankan server
composer run dev
```

Aplikasi akan berjalan di `http://localhost:8000`

## Akses Layanan

| Layanan | URL |
|---|---|
| Landing Page | http://localhost:8000 |
| Swagger UI | http://localhost:8000/api/v1/documentation |
| GraphiQL Playground | http://localhost:8000/graphiql |
| GraphQL Endpoint | http://localhost:8000/graphql |

## Autentikasi

Semua endpoint REST memerlukan API Key yang dikirim via header:

```
X-API-KEY: <api-key>
```

Default key (NIM):
```
102022400084
```

Generate key baru:
```bash
php artisan apikey:generate
```

Tambahkan hasil generate ke `.env`:
```env
API_KEY=hasil_generate_di_sini
```

## Menjalankan dengan Docker

```bash
# Jalankan semua service
docker compose up -d

# Akses di http://localhost:8000

# Matikan container
docker compose down
```

**Muhammad Fadhlan — 102022400084**
Sistem Informasi · Fakultas Rekayasa Industri · Telkom University
