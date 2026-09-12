import { app } from 'electron';
import path from 'path';

if (process.platform === 'win32') {
    try {
        process.chdir(path.dirname(process.execPath));
    } catch {
        // Chromium still needs icudtl.dat next to the exe.
    }

    if (app?.isPackaged) {
        app.commandLine.appendSwitch('lang', 'en-US');
        app.commandLine.appendSwitch('icu-data-dir', path.dirname(process.execPath));
    }
}
