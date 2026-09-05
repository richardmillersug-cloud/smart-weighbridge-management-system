# Smart Weighbridge — Customer Setup Guide

This guide is for **station owners and operators** installing the Smart Weighbridge app on the **weighbridge PC** (the Windows computer connected to the scale indicator).

---

## Choose your installer

| Version | Download | PHP required? | App opens in |
|---------|----------|---------------|--------------|
| **v1.1.0+ (recommended)** | `SmartWeighbridge-Native.exe` | **No** — bundled | Native desktop window |
| **v1.0.x (legacy)** | `SmartWeighbridge-Setup.exe` | **Yes** — install PHP 8.4 | Browser at 127.0.0.1 |

**Download:** [GitHub Releases](https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases)

> **Important:** The app must run on the **same PC** as the COM port. It cannot read the scale from another computer.

---

## Native app setup (v1.1.0+) — `SmartWeighbridge-Native.exe`

You need **MySQL 8 only**. PHP is already inside the installer (~200 MB download).

### Step N1 — Install MySQL 8

1. Install MySQL 8: [https://dev.mysql.com/downloads/installer/](https://dev.mysql.com/downloads/installer/)
2. Set a **root password** and remember it.
3. Create the database:

```sql
CREATE DATABASE smart_weighbridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step N2 — Run the native installer (important)

`SmartWeighbridge-Native.exe` is an **installer**, not the app itself.

1. **Move** the downloaded file out of **Downloads** to a local folder, e.g. `C:\Temp\`
2. **Do not** run it from OneDrive, a network drive, or a USB stick
3. Right‑click → **Run as administrator**
4. If Windows SmartScreen appears: **More info** → **Run anyway** (app is not code‑signed yet)
5. Complete the install wizard — use the default folder (local disk)
6. Tick **Create a desktop shortcut**
7. **Do not** launch from the downloaded `.exe` again after install

### Step N3 — Start the installed app

Open **Start Menu → Smart Weighbridge Management System** (or the desktop shortcut created by the installer).

The app opens in its **own window** — no browser, no `127.0.0.1` address bar.

### Step N4 — Configure `.env` and database

After first install, configure the app data folder (your supplier will provide the exact path, often under `%APPDATA%` or the install folder). Set at least:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_weighbridge
DB_USERNAME=root
DB_PASSWORD=YOUR_MYSQL_PASSWORD_HERE
WEIGHBRIDGE_DRIVER=xk3190
WEIGHBRIDGE_COM_PORT=COM1
```

Run first‑time database setup as directed by your supplier (`setup-station.ps1` or equivalent).

### If you see: `Invalid file descriptor to ICU data received`

This is an **Electron/Chromium startup error** — the desktop shell could not load its locale files. The app will **not** open until this is fixed.

| Try this | Why |
|----------|-----|
| Install from `C:\Temp\` (local disk), not Downloads/OneDrive | Network/sync paths break Electron |
| Run installer **as administrator** | Permission issues block bundled files |
| Complete the full install wizard | Double‑clicking the download is not enough |
| Launch from **Start Menu shortcut** after install | Correct working directory |
| Add install folder to **Windows Defender exclusions** | Antivirus can quarantine `icudtl.dat` |
| Re‑download `SmartWeighbridge-Native.exe` and reinstall | Corrupted download |

**Still broken?** Use the **legacy v1.0.1** installer (`SmartWeighbridge-Setup.exe`) from GitHub Releases — it uses PHP + browser mode and is verified on station PCs. Follow **Legacy setup** below.

---

## Legacy setup (v1.0.x) — `SmartWeighbridge-Setup.exe`

## Before you start

You will need:

| Item | Details |
|------|---------|
| **Windows PC** | Same PC where the XK3190-DS17 indicator is connected |
| **SmartWeighbridge-Setup.exe** | From [GitHub Releases v1.0.1](https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases/tag/v1.0.1) |
| **PHP 8.4** | Free download — see Step 1 |
| **MySQL 8** | Local database on this PC — see Step 1 |
| **Internet** | Required for cloud backup/sync to DigitalOcean |
| **Scale cable** | RS232 or USB‑serial from indicator to PC |

---

## Step 1 — Install PHP and MySQL on the station PC

These are installed **once** on the weighbridge computer.

### PHP 8.4

1. Download PHP for Windows: [https://windows.php.net/download/](https://windows.php.net/download/)
2. Choose **VS16 x64 Thread Safe** ZIP (PHP 8.4).
3. Extract to `C:\php` (or another folder).
4. Add that folder to **Windows PATH**:
   - Start → search **Environment Variables**
   - Edit **Path** → **New** → add `C:\php`
5. Open **Command Prompt** and confirm:

```text
php -v
```

6. In `C:\php`, copy `php.ini-development` to `php.ini`.
7. In `php.ini`, remove the `;` in front of these lines (uncomment them):

```ini
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_mysql
```

### MySQL 8

1. Install MySQL 8 (or use XAMPP with MySQL): [https://dev.mysql.com/downloads/installer/](https://dev.mysql.com/downloads/installer/)
2. Set a **root password** and remember it.
3. Open **MySQL Command Line** or **MySQL Workbench** and create the database:

```sql
CREATE DATABASE smart_weighbridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Step 2 — Run the Smart Weighbridge installer

1. Double‑click **`SmartWeighbridge-Setup.exe`**.
2. Follow the wizard (use the default install folder if unsure).
3. When asked, tick **Create a desktop shortcut**.
4. At the end of setup, optional steps appear:
   - **Edit .env configuration** — tick this (recommended)
   - **Run first-time database setup** — tick this after editing `.env`
   - **Launch Smart Weighbridge** — leave unticked until setup is complete

The app installs to:

```text
C:\Program Files\SmartWeighbridge
```

---

## Step 3 — Configure the `.env` file

When Notepad opens the `.env` file, set at least these values:

### Local database (required)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_weighbridge
DB_USERNAME=root
DB_PASSWORD=YOUR_MYSQL_PASSWORD_HERE
```

Replace `YOUR_MYSQL_PASSWORD_HERE` with the MySQL root password from Step 1.

### Weighbridge indicator (required for live weighing)

```env
WEIGHBRIDGE_DRIVER=xk3190
WEIGHBRIDGE_COM_PORT=COM1
WEIGHBRIDGE_BAUD_RATE=9600
```

- Check your COM port in **Device Manager → Ports (COM & LPT)**.
- If your port is `COM3`, change `WEIGHBRIDGE_COM_PORT=COM3`.

### Cloud backup (DigitalOcean — if provided by your supplier)

```env
CLOUD_SYNC_ENABLED=true
DB_CLOUD_HOST=your-cluster.db.ondigitalocean.com
DB_CLOUD_PORT=25060
DB_CLOUD_DATABASE=defaultdb
DB_CLOUD_USERNAME=doadmin
DB_CLOUD_PASSWORD=your_cloud_password
DB_CLOUD_SSL_CA=C:\Program Files\SmartWeighbridge\storage\certs\ca-certificate.crt
```

Your supplier will give you the cloud host, username, and password.

**Save** the file and close Notepad.

---

## Step 4 — First-time database setup

1. From the **Start Menu**, open **Smart Weighbridge → Station Setup**.
2. Wait until you see **Setup complete**.
3. If cloud sync is enabled, your supplier may ask you to run these once in **Command Prompt** (as Administrator):

```text
cd "C:\Program Files\SmartWeighbridge"
php artisan migrate --database=mysql_cloud
php artisan cloud:sync-full
```

---

## Step 5 — Cloud certificate (if using cloud backup)

1. Your supplier will send a file named **`ca-certificate.crt`**.
2. Copy it to:

```text
C:\Program Files\SmartWeighbridge\storage\certs\ca-certificate.crt
```

3. Confirm the path in `.env` matches (`DB_CLOUD_SSL_CA=...`).

---

## Step 6 — Connect and test the scale

1. Connect the **XK3190-DS17** to the PC with the serial/USB cable.
2. On the indicator, set **continuous transmit** at **9600‑8‑N‑1**.
3. In **Device Manager**, confirm the COM port (e.g. COM1).
4. Start the app (Step 7).
5. Sign in and open **Stations → Test Connection**.
6. Open **Weighing** and confirm:
   - Status shows **Online**
   - Live weight changes when something is on the scale
   - Footer shows **Driver: XK3190-DS17**

### If the scale shows Offline

| Check | Action |
|-------|--------|
| Cable | Firmly connected at PC and indicator |
| COM port | Match `.env` and **Stations** settings |
| Other software | Close any program using the COM port |
| Driver | Reboot PC; try a USB‑serial adapter if needed |

---

## Step 7 — Start the app (every day)

**To start:**

- Double‑click the **Smart Weighbridge** desktop shortcut,  
  **or**
- Open **Start Menu → Smart Weighbridge**

The browser opens at **http://127.0.0.1:8000**.

**To stop:**

- Start Menu → **Smart Weighbridge → Stop Smart Weighbridge**

---

## Step 8 — Sign in

After first setup, default accounts may be:

| Email | Password | Role |
|-------|----------|------|
| `admin@example.com` | `password` | System Administrator |
| `operator@example.com` | `password` | Bridge Handler |
| `auditor@example.com` | `password` | Auditor |

> **Change these passwords immediately** under **Administration → Users & Roles**.

---

## Daily operator workflow

1. Start **Smart Weighbridge** (desktop shortcut).
2. Sign in as **operator**.
3. Open **Weighing**.
4. Enter truck, customer, driver, and product details.
5. Wait for **STABLE**, then capture **Gross** and **Tare**.
6. Print the ticket.
7. Create invoice and record payment under **Billing**.

---

## Cloud sync (Admin)

If cloud backup is enabled, administrators can check sync status at:

**Administration → Cloud Sync**

| Button | When to use |
|--------|-------------|
| **Full Sync** | Push all local records to the cloud |
| **Retry Failed** | Retry records that failed to sync |

Cloud sync works best when the PC has internet and the app is running (the launcher starts the sync worker automatically).

---

## Updating to a new version

When your supplier sends a newer **`SmartWeighbridge-Setup.exe`**:

1. **Stop** the app — Start Menu → **Stop Smart Weighbridge**.
2. **Download** the latest release from your supplier (GitHub Releases link if provided).
3. **Run** the new `SmartWeighbridge-Setup.exe` — install to the **same folder** (usually `C:\Program Files\SmartWeighbridge`).
4. **Upgrade** — Start Menu → **Smart Weighbridge → Upgrade Station** (runs database migrations).
5. **Start** the app again — desktop shortcut **Smart Weighbridge**.

Your **`.env`**, local database, and tickets/invoices are **kept**. You do not need to run **Station Setup** again unless your supplier says so.

### For customers — install or update

**v1.1.1+:** [SmartWeighbridge-Native.exe](https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases/download/v1.1.1/SmartWeighbridge-Native.exe) — native desktop app (~200 MB, PHP bundled).

**v1.0.x:** [SmartWeighbridge-Setup.exe](https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases/download/v1.0.1/SmartWeighbridge-Setup.exe) — legacy browser mode (requires PHP + MySQL).

See **Native app setup** or **Legacy setup** at the top of this guide.

---

## Support checklist

If something is wrong, note:

1. Exact error message (screenshot helps).
2. COM port from Device Manager.
3. Whether **Weighing** shows Online or Offline.
4. Whether **Cloud Sync** page shows Reachable: Online or Offline.
5. Windows version and whether PHP/MySQL are installed (`php -v` in Command Prompt).

Contact your supplier with this information.

---

## Quick reference

| Task | How |
|------|-----|
| Start app | Desktop shortcut **Smart Weighbridge** |
| Stop app | **Stop Smart Weighbridge** (Start Menu) |
| Edit settings | `.env` file in install folder |
| Scale COM port | `.env` → `WEIGHBRIDGE_COM_PORT` and **Stations** in app |
| Cloud status | **Administration → Cloud Sync** |
| Change passwords | **Administration → Users & Roles** |
| **Update app** | New Setup.exe → **Upgrade Station** → restart |

---

*Smart Weighbridge Management System — station deployment guide*
