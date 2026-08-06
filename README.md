# Smart Weighbridge Management System

Laravel 12 + Livewire 3 weighbridge app for truck weighing, tickets, billing, payments, demandings, and audit tracking. Live weight can come from an **XK3190-A12** indicator on a local Windows COM port, or from a built-in simulator for setup without hardware.

---

## Requirements

| Software | Version / notes |
|----------|-----------------|
| PHP | **8.4+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `fileinfo`, `tokenizer`, `xml` |
| Composer | 2.x |
| MySQL | 8.x |
| Node.js / npm | 20+ (to build frontend assets) |
| OS for live scale | **Windows** PC that has the USB/RS232 adapter (COM port). PHP must run on that same PC. |

---

## Setup (download → first run)

### 1. Get the code

```bash
git clone https://github.com/richardmillersug-cloud/smart-weighbridge-management-system.git "Smart weighbridge management system"
cd "Smart weighbridge management system"
```

Or download the ZIP from GitHub and extract it.

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Environment file

```bash
# Windows
copy .env.example .env

# Linux / macOS
cp .env.example .env
```

Generate the app key:

```bash
php artisan key:generate
```

Edit `.env` and set at least:

```env
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_weighbridge
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

### 4. Create the database

In MySQL:

```sql
CREATE DATABASE smart_weighbridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Migrate and seed

```bash
php artisan migrate --seed
```

This creates tables, roles, sample users, a default station (XK3190-A12), and demo transactions.

### 6. Build the frontend

```bash
npm install
npm run build
```

(Use `npm run dev` only while developing with Vite hot reload.)

### 7. Link storage (optional, for uploads)

```bash
php artisan storage:link
```

### 8. Start the app

```bash
php artisan serve
```

Open **http://127.0.0.1:8000** and sign in.

---

## Sample logins (after seeding)

| Email | Password | Role |
|-------|----------|------|
| `admin@example.com` | `password` | System Administrator |
| `operator@example.com` | `password` | Bridge Handler |
| `auditor@example.com` | `password` | Auditor |

Change these passwords before any real production use.

---

## Scale / XK3190-A12 setup

### Without hardware (simulation)

In `.env`:

```env
WEIGHBRIDGE_DRIVER=dummy
```

The weighing screen shows simulated live weight. Useful for training and development.

### With XK3190-A12 on this PC

1. Connect the indicator USB/RS232 adapter and note the COM port in **Device Manager** (e.g. `COM3`).
2. In `.env`:

```env
WEIGHBRIDGE_DRIVER=xk3190
WEIGHBRIDGE_COM_PORT=COM3
WEIGHBRIDGE_BAUD_RATE=9600
```

3. In the app, open **Stations** and set the same **COM port** and baud on the default station (station settings override `.env`).
4. Restart PHP (`php artisan serve`), then use **Test Connection** on the station.
5. On **Weighing**, confirm **Online / STABLE** and that the footer shows **Driver: XK3190-A12**.

**Important:** Host Laravel on the **same Windows PC** as the COM adapter. A remote server cannot open the operator’s local serial port.

Typical serial settings for XK3190-A12 continuous mode: **9600-8-N-1**.

---

## Daily operator use (short)

1. Sign in as operator.
2. Open **Weighing**.
3. Enter truck / goods / customer / driver (type freely; new values are saved when you create the ticket).
4. Wait for **STABLE**, capture **Gross**, then **Tare** (or use preset tare / net mode as configured).
5. Print the ticket; create invoice with **Amount Payable**; collect payment under **Billing** or pay customer demands under **Demandings**.

---

## Modules

| Module | Path | Notes |
|--------|------|-------|
| Dashboard | `/dashboard` | Stats and recent activity |
| Weighing | `/weighbridge` | Live weight, tickets |
| Tickets | `/tickets` | History, print |
| Billing | `/invoices` | Invoices + payments tabs |
| Demandings | `/demandings` | Pay outstanding per customer |
| Master data | customers, vehicles, drivers, products, etc. | Type-or-create from weighing |
| Stations | `/stations` | Indicator / COM settings |
| Reports / Audit | `/reports`, `/audit` | Oversight |
| Users / Settings | `/users`, `/settings` | Admin |

---

## Roles

- **System Administrator** — full access
- **Bridge Handler** — weighing, tickets, billing, payments
- **Auditor** — read-only reports and audit trail

---

## Hardware driver layout

```
app/Services/Weighbridge/
├── WeightReaderInterface.php
├── WeightReading.php
├── DummyWeightReaderService.php   # simulation
├── SerialWeightReaderService.php  # COM read + XK3190 parse
└── XK3190RS232ReaderService.php   # alias for XK3190-A12
```

Select driver with `WEIGHBRIDGE_DRIVER` in `.env` (`dummy` | `xk3190` | `serial`).

---

## Testing

```bash
php artisan test
```

---

## What not to commit

- Never commit `.env` (secrets and local DB passwords).
- `vendor/`, `node_modules/`, and local PHP ini files stay out of git (see `.gitignore`).

---

## Tech stack

Laravel 12 · PHP 8.4 · MySQL 8 · Livewire 3 · Tailwind CSS 4 · Vite · Spatie Permission · Chart.js
