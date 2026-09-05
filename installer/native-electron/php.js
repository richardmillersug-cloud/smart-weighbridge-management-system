import { execFile } from 'child_process';
import fs from 'fs';
import fs_extra from 'fs-extra';
import { dirname, join } from 'path';
import { promisify } from 'util';
import unzip from 'yauzl';

const execFileAsync = promisify(execFile);
const { removeSync, ensureDirSync } = fs_extra;

const isBuilding = Boolean(process.env.NATIVEPHP_BUILDING);
const phpBinaryPath = process.env.NATIVEPHP_PHP_BINARY_PATH;
const phpVersion = process.env.NATIVEPHP_PHP_BINARY_VERSION;

const isArm64 = isBuilding ? process.argv.includes('--arm64') : process.arch.includes('arm64');
const isWindows = isBuilding ? process.argv.includes('--win') : process.platform.includes('win32');
const isLinux = isBuilding ? process.argv.includes('--linux') : process.platform.includes('linux');
const isDarwin = isBuilding ? process.argv.includes('--mac') : process.platform.includes('darwin');

const platform = {
    os: false,
    arch: false,
    phpBinary: 'php',
};

if (isWindows) {
    platform.os = 'win';
    platform.arch = 'x64';
    platform.phpBinary += '.exe';
}

if (isLinux) {
    platform.os = 'linux';
    platform.arch = 'x64';
}

if (isDarwin) {
    platform.os = 'mac';
    platform.arch = 'x64';
}

if (isArm64) {
    platform.arch = 'arm64';
}

if (isBuilding) {
    platform.arch = process.argv.includes('--x64') ? 'x64' : platform.arch;
    platform.arch = process.argv.includes('--arm64') ? 'arm64' : platform.arch;
}

const phpVersionZip = 'php-' + phpVersion + '.zip';
const binarySrcDir = join(phpBinaryPath, platform.os, platform.arch, phpVersionZip);
const binaryDestDir = join(process.env.NATIVEPHP_BUILD_PATH, 'php');

console.log('Binary Source: ', binarySrcDir);
console.log('Binary Filename: ', platform.phpBinary);
console.log('PHP version: ' + phpVersion);

function extractZip(source, destination) {
    return new Promise((resolve, reject) => {
        unzip.open(source, { lazyEntries: true }, (err, zipfile) => {
            if (err) {
                reject(err);
                return;
            }

            let pending = 0;

            const done = () => {
                if (pending === 0) {
                    zipfile.close();
                }
            };

            zipfile.on('close', resolve);
            zipfile.on('error', reject);

            zipfile.on('entry', (entry) => {
                if (/\/$/.test(entry.fileName)) {
                    zipfile.readEntry();
                    return;
                }

                pending++;
                zipfile.openReadStream(entry, (streamErr, readStream) => {
                    if (streamErr) {
                        reject(streamErr);
                        return;
                    }

                    const destPath = join(destination, entry.fileName);
                    ensureDirSync(dirname(destPath));
                    const writeStream = fs.createWriteStream(destPath);

                    readStream.on('error', reject);
                    writeStream.on('error', reject);
                    writeStream.on('close', () => {
                        if (platform.phpBinary.endsWith('.exe') && entry.fileName.endsWith('.exe')) {
                            fs.chmod(destPath, 0o755, () => {});
                        }

                        pending--;
                        zipfile.readEntry();
                        done();
                    });

                    readStream.pipe(writeStream);
                });
            });

            zipfile.readEntry();
        });
    });
}

if (platform.phpBinary) {
    try {
        console.log('Unzipping PHP binary from ' + binarySrcDir + ' to ' + binaryDestDir);
        removeSync(binaryDestDir);
        ensureDirSync(binaryDestDir);
        await extractZip(binarySrcDir, binaryDestDir);

        const phpPath = join(binaryDestDir, platform.phpBinary);
        if (!fs.existsSync(phpPath)) {
            const found = fs.readdirSync(binaryDestDir, { recursive: true }).find((f) => String(f).endsWith(platform.phpBinary));
            if (!found) {
                throw new Error(`PHP binary not found after extract: ${phpPath}`);
            }
        }

        console.log('Copied PHP binary to ', phpPath);
    } catch (e) {
        console.error('Error copying PHP binary', e);
        process.exit(1);
    }
}
