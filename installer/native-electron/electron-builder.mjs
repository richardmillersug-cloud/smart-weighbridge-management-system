import { exec } from 'child_process';
import { existsSync } from 'fs';
import { join } from 'path';

const appUrl = process.env.APP_URL;
const appId = process.env.NATIVEPHP_APP_ID;
const appName = process.env.NATIVEPHP_APP_NAME;
const isBuilding = process.env.NATIVEPHP_BUILDING;
const appAuthor = process.env.NATIVEPHP_APP_AUTHOR;
const fileName = process.env.NATIVEPHP_APP_FILENAME;
const appVersion = process.env.NATIVEPHP_APP_VERSION;
const appCopyright = process.env.NATIVEPHP_APP_COPYRIGHT;
const deepLinkProtocol = process.env.NATIVEPHP_DEEPLINK_SCHEME;
const updaterEnabled = process.env.NATIVEPHP_UPDATER_ENABLED === 'true';
const deleteAppDataOnUninstall = process.env.NATIVEPHP_NSIS_DELETE_APP_DATA === 'true';

const azureEndpoint = process.env.NATIVEPHP_AZURE_ENDPOINT;
const azureCertificateProfileName = process.env.NATIVEPHP_AZURE_CERTIFICATE_PROFILE_NAME;
const azureCodeSigningAccountName = process.env.NATIVEPHP_AZURE_CODE_SIGNING_ACCOUNT_NAME;

const isWindows = process.argv.includes('--win');
const isLinux = process.argv.includes('--linux');
const isDarwin = process.argv.includes('--mac');

let targetOs;

if (isWindows) {
    targetOs = 'win';
}

if (isLinux) {
    targetOs = 'linux';
}

if (isDarwin) {
    targetOs = 'mac';
}

let updaterConfig = {};

try {
    updaterConfig = process.env.NATIVEPHP_UPDATER_CONFIG;
    updaterConfig = JSON.parse(updaterConfig);
} catch {
    updaterConfig = {};
}

if (isBuilding) {
    console.log('  • updater config', updaterConfig);
}

const extraFiles = [
    {
        from: 'node_modules/electron/dist/icudtl.dat',
        to: '.',
    },
    {
        from: 'node_modules/electron/dist/locales',
        to: 'locales',
    },
    {
        from: join(process.env.APP_PATH || '', 'installer', 'native-electron', 'Start Smart Weighbridge.cmd'),
        to: '.',
    },
];

const extrasPath = join(process.env.APP_PATH || '', 'extras');
if (existsSync(extrasPath)) {
    extraFiles.push({
        from: extrasPath,
        to: 'extras',
        filter: ['**/*'],
    });
}

export default {
    appId: appId,
    productName: appName,
    copyright: appCopyright,
    directories: {
        buildResources: 'build',
        output: isBuilding ? join(process.env.APP_PATH, 'nativephp', 'electron', 'dist') : undefined,
    },
    asar: true,
    asarUnpack: ['**/icudtl.dat', '**/locales/**', '**/*.pak', '**/*.node'],
    electronLanguages: ['en-US'],
    files: [
        '!**/.vscode/*',
        '!src/*',
        '!dist/*',
        '!electron.vite.config.{js,ts,mjs,cjs}',
        '!{.eslintignore,.eslintrc.cjs,.prettierignore,.prettierrc.yaml,dev-app-update.yml,CHANGELOG.md,README.md}',
        '!{.env,.env.*,.npmrc,pnpm-lock.yaml}',
    ],
    beforePack: async (context) => {
        const arch = {
            1: 'x64',
            3: 'arm64',
        }[context.arch];

        if (arch === undefined) {
            console.error('Cannot build PHP for unsupported architecture');
            process.exit(1);
        }

        console.log(`  • building php binary - exec php.js --${targetOs} --${arch}`);
        exec(`node php.js --${targetOs} --${arch}`);
    },
    afterPack: async (context) => {
        const icu = join(context.appOutDir, 'icudtl.dat');
        if (!existsSync(icu)) {
            console.error('ICU data missing from pack (desktop window will fail):', icu);
            process.exit(1);
        }

        console.log('  • icudtl.dat present at', icu);
    },
    afterSign: 'build/notarize.js',
    win: {
        executableName: fileName,
        ...(azureEndpoint && azureCertificateProfileName && azureCodeSigningAccountName
            ? {
                  azureSignOptions: {
                      endpoint: azureEndpoint,
                      certificateProfileName: azureCertificateProfileName,
                      codeSigningAccountName: azureCodeSigningAccountName,
                  },
              }
            : {}),
    },
    nsis: {
        artifactName: appName + '-${version}-setup.${ext}',
        shortcutName: 'Smart Weighbridge',
        uninstallDisplayName: '${productName}',
        createDesktopShortcut: 'always',
        deleteAppDataOnUninstall: deleteAppDataOnUninstall,
    },
    protocols: {
        name: deepLinkProtocol,
        schemes: [deepLinkProtocol],
    },
    mac: {
        entitlementsInherit: 'build/entitlements.mac.plist',
        artifactName: appName + '-${version}-${arch}.${ext}',
        extendInfo: {
            NSCameraUsageDescription: "Application requests access to the device's camera.",
            NSMicrophoneUsageDescription: "Application requests access to the device's microphone.",
            NSDocumentsFolderUsageDescription: "Application requests access to the user's Documents folder.",
            NSDownloadsFolderUsageDescription: "Application requests access to the user's Downloads folder.",
        },
    },
    dmg: {
        artifactName: appName + '-${version}-${arch}.${ext}',
    },
    linux: {
        target: ['AppImage', 'deb'],
        maintainer: appUrl,
        category: 'Utility',
    },
    appImage: {
        artifactName: appName + '-${version}.${ext}',
    },
    npmRebuild: false,
    extraMetadata: {
        name: fileName,
        homepage: appUrl,
        version: appVersion,
        author: appAuthor,
    },
    extraResources: [
        {
            from: process.env.NATIVEPHP_BUILD_PATH,
            to: 'build',
            filter: ['**/*', '!{.git}'],
        },
    ],
    extraFiles,
    ...(updaterEnabled ? { publish: updaterConfig } : {}),
};
