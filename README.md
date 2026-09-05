# Smart Weighbridge Management System

Laravel 12 + Livewire 3 weighbridge app for truck weighing, tickets, billing, payments, demandings, and audit tracking. Live weight comes from a **Yaohua XK3190-DS17** indicator (or compatible XK3190 models) on a local Windows COM port, or from a built-in simulator for training without hardware.

**Repository:** [github.com/richardmillersug-cloud/smart-weighbridge-management-system](https://github.com/richardmillersug-cloud/smart-weighbridge-management-system)

> **Customer / station install:** install PHP 8.4+ and MySQL 8, run the Windows installer, then complete the first-run setup screen. See **[CUSTOMER-SETUP.md](CUSTOMER-SETUP.md)**.

---

## Requirements

| Software | Version / notes |
|----------|-----------------|
| PHP | **8.4+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `fileinfo`, `tokenizer`, `xml`, `curl` |
| Composer | 2.x |
| MySQL | **8.x** (production / recommended) |
| Node.js / npm | 20+ (to build frontend assets) |
| OS for live scale | **Windows** station PC with the indicator COM port. PHP must run on that same PC. |

For local development without MySQL, SQLite can be used temporarily (see [Quick local setup](#quick-local-setup-sqlite) below).

---

## Setup (clone → first run)

### 1. Get the code

```bash
git clone https://github.com/richardmillersug-cloud/smart-weighbridge-management-system.git
cd smart-weighbridge-management-system
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

This creates tables, roles, sample users, a default station (**XK3190-DS17 on COM1**), and demo transactions.

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

## Station computer deployment (production)

Use this flow on the **physical weighbridge PC** where the indicator is connected.

1. Install PHP 8.4, Composer, Node.js, and **local MySQL** on the station PC.
2. Clone the repo and complete [Setup](#setup-clone--first-run) steps 2–7.
3. Enable required PHP extensions in `php.ini` (especially `openssl`, `curl`, `pdo_mysql`, `mbstring`).
4. Connect the **XK3190-DS17** to the PC via RS232 or USB‑serial and confirm the COM port in **Device Manager → Ports (COM & LPT)**.
5. Set live indicator mode in `.env`:

```env
WEIGHBRIDGE_DRIVER=xk3190
WEIGHBRIDGE_COM_PORT=COM1
WEIGHBRIDGE_BAUD_RATE=9600
WEIGHBRIDGE_DATA_BITS=8
WEIGHBRIDGE_STOP_BITS=1
WEIGHBRIDGE_PARITY=none
WEIGHBRIDGE_FLOW_CONTROL=none
```

6. In the app, open **Stations** and confirm the default station matches your COM port and baud rate (station settings override `.env`).
7. Restart the app (`php artisan serve`), then use **Stations → Test Connection**.
8. Open **Weighing** and confirm **Online**, live weight updating, and footer showing **Driver: XK3190-DS17**.
9. Optional: enable [Local + cloud database](#local--cloud-database-real-time-sync) for DigitalOcean backup.

**Important:** Laravel must run on the **same Windows PC** as the COM port. A remote server cannot read the operator’s local serial port.

---

## Quick local setup (SQLite)

For development or first-time testing when MySQL is not installed yet:

1. Create an empty SQLite file:

```bash
# Windows PowerShell
New-Item -ItemType File -Path database\database.sqlite -Force
```

2. In `.env`, switch the database driver:

```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=smart_weighbridge
# DB_USERNAME=root
# DB_PASSWORD=
```

3. Run migrations and seed:

```bash
php artisan migrate --seed
```

Switch back to MySQL before production use.

---

## Local + cloud database (real-time sync)

Recommended production layout for a station PC:

```text
[Station PC]
  App + COM1 indicator
  Local MySQL  ← primary (always)
        |
        | queue jobs (seconds)
        v
  DigitalOcean Managed MySQL  ← cloud mirror
```

### How it works

1. Every ticket, invoice, payment, and master-data change saves to **local MySQL** first.
2. A queue job immediately pushes the row to **DigitalOcean** (`mysql_cloud`).
3. If the internet drops, weighing continues locally; failed jobs retry when back online.
4. Local DB remains the **source of truth**.

### `.env` on the station PC

```env
# Local primary database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_weighbridge
DB_USERNAME=root
DB_PASSWORD=local_password

# DigitalOcean managed MySQL
CLOUD_SYNC_ENABLED=true
DB_CLOUD_HOST=your-cluster.db.ondigitalocean.com
DB_CLOUD_PORT=25060
DB_CLOUD_DATABASE=smart_weighbridge
DB_CLOUD_USERNAME=doadmin
DB_CLOUD_PASSWORD=your_do_password
DB_CLOUD_SSL_CA=C:\smart-weighbridge-management-system\storage\certs\ca-certificate.crt

# Keep sessions/cache local (works better when cloud is remote)
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database
```

### One-time cloud setup

1. Create a **DigitalOcean Managed MySQL** cluster and database.
2. Add the station PC **public IP** under **Trusted sources**.
3. Download the CA certificate into `storage/certs/`.
4. Run migrations on the cloud database once:

```bash
php artisan migrate --database=mysql_cloud
php artisan cloud:sync-full
```

### Always-on queue worker (required for near real-time sync)

In a second terminal (or Windows Task Scheduler at startup):

```bash
php artisan queue:work --tries=5
```

### Cloud sync commands

| Command | Purpose |
|---------|---------|
| `php artisan cloud:sync-status` | Check connection and recent sync log |
| `php artisan cloud:sync-full` | Push all local records to cloud |
| `php artisan cloud:sync-retry` | Retry failed sync rows |

---

## Sample logins (after seeding)

| Email | Password | Role |
|-------|----------|------|
| `admin@example.com` | `password` | System Administrator |
| `operator@example.com` | `password` | Bridge Handler |
| `auditor@example.com` | `password` | Auditor |

Change these passwords before any real production use.

---

## Scale / XK3190-DS17 setup

### Without hardware (simulation)

In `.env`:

```env
WEIGHBRIDGE_DRIVER=dummy
```

The weighing screen shows simulated live weight. Useful for training and development.

### With XK3190-DS17 on this PC

1. Connect the indicator to the station PC (RS232 to onboard COM port, or USB‑serial adapter).
2. Open **Device Manager → Ports (COM & LPT)** and note the COM port (e.g. `COM1`).
3. On the indicator, set serial output to **continuous transmit** at **9600‑8‑N‑1**.
4. In `.env`, set the driver and COM port (example for onboard **COM1**):

```env
WEIGHBRIDGE_DRIVER=xk3190
WEIGHBRIDGE_COM_PORT=COM1
WEIGHBRIDGE_BAUD_RATE=9600
```

5. In **Stations**, set the same COM port and baud on the default station.
6. Restart PHP (`php artisan serve`), run **Test Connection**, then open **Weighing**.
7. Confirm **Online / STABLE**, live weight changes with the scale, and footer shows **Driver: XK3190-DS17**.

Compatible indicators using the same Yaohua continuous RS232 format (including **XK3190-A12**) also work with `WEIGHBRIDGE_DRIVER=xk3190`.

### Troubleshooting

| Symptom | Likely cause | Fix |
|---------|--------------|-----|
| **Offline** | Wrong COM port, cable unplugged, or port in use | Check Device Manager; close other serial software; update **Stations** COM port |
| **Online but weight stuck** | Parser/cache issue or indicator not in continuous mode | Confirm continuous output on DS17; restart app; run **Test Connection** |
| **Permission denied on COM1** | Built-in port disabled or locked | Reboot; check BIOS/driver; try USB‑serial adapter and use its COM port |
| **Weight wrong by factor of 10** | Decimal/format mismatch on indicator | Match indicator serial format to continuous mode in DS17 settings |

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
| Cloud Sync | `/cloud-sync` | Admin — cloud DB status and sync actions |

---

## Windows installer (EXE)

Build and publish **`SmartWeighbridge-Setup.exe`** for station PCs. See **[installer/README.md](installer/README.md)** for full steps.

**Publish a release (customers download updates from GitHub):**

```powershell
powershell -ExecutionPolicy Bypass -File installer\scripts\publish-release.ps1
```

Or tag a version for automated CI build:

```powershell
git tag v1.0.0
git push origin v1.0.0
```

**Releases:** [github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases](https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases)

After install, operators launch the app with **`SmartWeighbridge.bat`** (starts web server, queue worker, and opens the browser). To update, run a new Setup.exe then **Upgrade Station** from the Start Menu.

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
├── SerialWeightReaderService.php  # COM read + XK3190/DS17 parse
└── XK3190RS232ReaderService.php   # alias for live serial indicators
```

Select driver with `WEIGHBRIDGE_DRIVER` in `.env` (`dummy` | `xk3190` | `serial`).

Live weight parsing supports **XK3190-DS17 fixed-width frames** (e.g. `=0001234`) and compatible Yaohua continuous formats. Minimum **100 kg** applies when **capturing** a weight for a ticket, not for live display.

---

## Testing

```bash
php artisan test
```

Serial frame parsing tests live in `tests/Unit/SerialWeightParserTest.php`.

---

## Git workflow

| Branch | Purpose |
|--------|---------|
| `master` | Main release line |
| `user/richardweighbridgeUG` | User/station deployment branch |

Push to: `https://github.com/richardmillersug-cloud/smart-weighbridge-management-system.git`

---

## What not to commit

- Never commit `.env` (secrets and local DB passwords).
- Do not commit `vendor/`, `node_modules/`, `composer.phar`, or local PHP ini files (see `.gitignore`).

---

## Tech stack

Laravel 12 · PHP 8.4 · MySQL 8 · Livewire 3 · Tailwind CSS 4 · Vite · Spatie Permission · Chart.js · XK3190-DS17 RS232
