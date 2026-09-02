; Smart Weighbridge Management System — Windows installer
; Requires Inno Setup 6 or 7: https://jrsoftware.org/isinfo.php
; Run build-release.ps1 first, then compile this script.
; Version: see VERSION file and installer/version.iss

#include "version.iss"

#define AppName "Smart Weighbridge"
#define AppPublisher "Smart Weighbridge"
#define AppURL "https://github.com/richardmillersug-cloud/smart-weighbridge-management-system"
#define ReleaseDir "..\dist\SmartWeighbridgeRelease"
#define OutputDir "..\dist"

[Setup]
AppId={{A8F3C2E1-9B4D-4A7E-8F1C-2D6E5A9B3C7D}
AppName={#AppName}
AppVersion={#AppVersion}
AppPublisher={#AppPublisher}
AppPublisherURL={#AppURL}
AppSupportURL={#AppURL}
DefaultDirName={autopf}\SmartWeighbridge
UsePreviousAppDir=yes
DefaultGroupName={#AppName}
DisableProgramGroupPage=yes
OutputDir={#OutputDir}
OutputBaseFilename=SmartWeighbridge-Setup
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
PrivilegesRequired=admin
ArchitecturesInstallIn64BitMode=x64compatible
SetupIconFile=assets\app-icon.ico
UninstallDisplayIcon={app}\installer\assets\app-icon.ico

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"

[Tasks]
Name: "desktopicon"; Description: "Create a desktop shortcut"; GroupDescription: "Shortcuts:"; Flags: checkedonce

[Files]
Source: "{#ReleaseDir}\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs
Source: "{#ReleaseDir}\installer\env\.env.station.example"; DestDir: "{app}"; DestName: ".env"; Flags: onlyifdoesntexist

[Icons]
Name: "{group}\{#AppName}"; Filename: "{app}\SmartWeighbridge.bat"; WorkingDir: "{app}"; IconFilename: "{app}\installer\assets\app-icon.ico"
Name: "{group}\Stop {#AppName}"; Filename: "{app}\Stop SmartWeighbridge.bat"; WorkingDir: "{app}"
Name: "{group}\Customer Setup Guide"; Filename: "{app}\CUSTOMER-SETUP.md"; WorkingDir: "{app}"
Name: "{group}\Upgrade Station"; Filename: "powershell.exe"; Parameters: "-ExecutionPolicy Bypass -File ""{app}\installer\scripts\upgrade-station.ps1"""; WorkingDir: "{app}"
Name: "{group}\Station Setup"; Filename: "powershell.exe"; Parameters: "-ExecutionPolicy Bypass -File ""{app}\installer\scripts\setup-station.ps1"""; WorkingDir: "{app}"
Name: "{autodesktop}\{#AppName}"; Filename: "{app}\SmartWeighbridge.bat"; WorkingDir: "{app}"; IconFilename: "{app}\installer\assets\app-icon.ico"; Tasks: desktopicon

[Run]
Filename: "powershell.exe"; Parameters: "-ExecutionPolicy Bypass -File ""{app}\installer\scripts\setup-station.ps1"""; Description: "Run first-time database setup"; Flags: postinstall skipifsilent unchecked
Filename: "powershell.exe"; Parameters: "-ExecutionPolicy Bypass -File ""{app}\installer\scripts\upgrade-station.ps1"""; Description: "Run database migrations (after an update)"; Flags: postinstall skipifsilent unchecked
Filename: "notepad.exe"; Parameters: "{app}\.env"; Description: "Edit .env configuration"; Flags: postinstall skipifsilent unchecked
Filename: "{app}\SmartWeighbridge.bat"; Description: "Launch {#AppName}"; Flags: postinstall skipifsilent nowait unchecked

[UninstallRun]
Filename: "{app}\Stop SmartWeighbridge.bat"; RunOnceId: "StopSmartWeighbridge"; Flags: runhidden
