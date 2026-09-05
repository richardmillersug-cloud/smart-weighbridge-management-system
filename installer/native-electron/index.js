import NativePHP from '#plugin';
import { app, dialog } from 'electron';
import { execSync } from 'child_process';
import { existsSync } from 'fs';
import path from 'path';

import fixPath from 'fix-path';
fixPath();

// Chromium ICU data (icudtl.dat) must resolve from the install directory on Windows.
if (process.platform === 'win32' && app.isPackaged) {
    process.chdir(path.dirname(process.execPath));
}

const buildPath = path.resolve(import.meta.dirname, import.meta.env.MAIN_VITE_NATIVEPHP_BUILD_PATH);
const defaultIcon = path.join(buildPath, 'icon.png');
const certificate = path.join(buildPath, 'cacert.pem');
const appPath = path.join(buildPath, 'app');
const bundledPhp = path.join(buildPath, 'php', process.platform === 'win32' ? 'php.exe' : 'php');

function resolvePhpBinary() {
    const fromEnv = process.env.PHP_BINARY || process.env.NATIVEPHP_PHP_BINARY;
    if (fromEnv && existsSync(fromEnv)) {
        return fromEnv;
    }

    try {
        const command = process.platform === 'win32' ? 'where php' : 'which php';
        const found = execSync(command, { encoding: 'utf8' }).trim().split(/\r?\n/)[0];
        if (found && existsSync(found)) {
            return found;
        }
    } catch {
        // PATH may not include PHP yet.
    }

    const candidates = [
        'C:\\php\\php.exe',
        'C:\\php84\\php.exe',
        'C:\\xampp\\php\\php.exe',
    ];

    for (const candidate of candidates) {
        if (existsSync(candidate)) {
            return candidate;
        }
    }

    if (existsSync(bundledPhp)) {
        return bundledPhp;
    }

    return null;
}

const phpBinary = resolvePhpBinary();

if (!phpBinary) {
    app.whenReady().then(async () => {
        await dialog.showMessageBox({
            type: 'error',
            title: 'Smart Weighbridge',
            message: 'PHP 8.4+ is required',
            detail: 'Install PHP 8.4+ and MySQL 8 on this PC. Add PHP to PATH, then start Smart Weighbridge from the Start Menu.',
        });
        app.quit();
    });
} else {
    NativePHP.bootstrap(app, defaultIcon, phpBinary, certificate, appPath);
}
