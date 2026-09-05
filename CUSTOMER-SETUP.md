# Smart Weighbridge — Customer Setup Guide

Install on the **weighbridge PC** (the Windows computer connected to the scale indicator). The app cannot read the scale from another computer.

---

## What you need (once)

| Software | Why |
|----------|-----|
| **PHP 8.4+** | Must be installed and on PATH (`php -v` in Command Prompt) |
| **MySQL 8** | Local database on this PC |
| **Smart Weighbridge installer** | From [GitHub Releases](https://github.com/richardmillersug-cloud/smart-weighbridge-management-system/releases) |

PHP: [windows.php.net](https://windows.php.net/download/) — enable `pdo_mysql`, `mbstring`, `openssl`, `curl` in `php.ini`.

MySQL: [dev.mysql.com/downloads/installer](https://dev.mysql.com/downloads/installer/) — set a root password and remember it. You do **not** create the database yourself.

---

## Install and start

1. Download **`SmartWeighbridge-Native.exe`** (or **`SmartWeighbridge-Setup.exe`**).
2. Move it to `C:\Temp` (not Downloads / OneDrive). Right‑click → **Run as administrator**.
3. If SmartScreen appears: **More info → Run anyway**.
4. Finish the wizard — default folder `C:\Program Files\SmartWeighbridge`. Tick the desktop shortcut.
5. Launch from the **Start Menu** or desktop shortcut (not the downloaded installer).

If PHP is missing, the app stops and tells you to install PHP 8.4+ and add it to PATH.

---

## First launch — station setup

The app opens a setup screen (not login) until the station is ready.

1. Confirm PHP and MySQL show as ready. Start MySQL if it is not running.
2. Enter the **MySQL password** for this PC (and host/user if you did not use the defaults).
3. Enter the scale **COM port** (Device Manager → Ports). Example: `COM1` or `COM3`.
4. **Cloud backup (optional):** tick Enable cloud sync and enter the DigitalOcean host, user, password, and CA certificate your supplier gave you. You can skip this and set it later under **Administration → Cloud Sync**.
5. Click **Create database and finish setup**.

The app creates `smart_weighbridge`, runs migrations, and seeds the first accounts. Then sign in.

| Email | Password | Role |
|-------|----------|------|
| `admin@example.com` | `password` | System Administrator |
| `operator@example.com` | `password` | Bridge Handler |
| `auditor@example.com` | `password` | Auditor |

Change these passwords immediately under **Administration → Users & Roles**.

If cloud sync was enabled but the cloud was unreachable, the local station still works. Fix the cloud details later and use **Full Sync** on the Cloud Sync page.

---

## Every day

- **Start:** desktop shortcut or Start Menu → **Smart Weighbridge**
- **Stop:** Start Menu → **Stop Smart Weighbridge**
- Sign in as **operator**, open **Weighing**, capture Gross/Tare when STABLE, print ticket, bill under **Billing**

---

## Updates

Install the new Setup/Native `.exe` into the **same folder**. Start the app — it applies database migrations automatically. Tickets, invoices, and settings are kept.

---

## If the native window will not open

Try the legacy installer (`SmartWeighbridge-Setup.exe`) from GitHub Releases. Same first-run setup screen. Install PHP + MySQL first.

| Symptom | What to try |
|---------|-------------|
| PHP is required | Install PHP 8.4+, add to PATH, open a new Command Prompt, run `php -v` |
| MySQL not running | Start the MySQL Windows service, then continue setup |
| `Invalid file descriptor to ICU data` | Install from `C:\Temp` as administrator; launch from Start Menu |

---

*Smart Weighbridge Management System — station deployment guide*
