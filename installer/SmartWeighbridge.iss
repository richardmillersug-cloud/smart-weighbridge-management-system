; Smart Weighbridge Management System — Windows installer
; Requires Inno Setup 6: https://jrsoftware.org/isinfo.php
; Run build-release.ps1 first, then compile this script.

#define AppName "Smart Weighbridge"
#define AppVersion "1.0.0"
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
DefaultGroupName={#AppName}
DisableProgramGroupPage=yes
OutputDir={#OutputDir}
OutputBaseFilename=SmartWeighbridge-Setup
Compression=lzma2/ultra64
SolidCompression=yes
WizardStyle=modern
PrivilegesRequired=admin
ArchitecturesInstallIn64BitMode=x64compatible
UninstallDisplayIcon={app}\SmartWeighbridge.bat

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"

[Tasks]
Name: "desktopicon"; Description: "Create a desktop shortcut"; GroupDescription: "Shortcuts:"; Flags: checkedonce

[Files]
Source: "{#ReleaseDir}\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs

[Icons]
Name: "{group}\{#AppName}"; Filename: "{app}\SmartWeighbridge.bat"; WorkingDir: "{app}"
Name: "{group}\Stop {#AppName}"; Filename: "{app}\Stop SmartWeighbridge.bat"; WorkingDir: "{app}"
Name: "{group}\Station Setup"; Filename: "powershell.exe"; Parameters: "-ExecutionPolicy Bypass -File ""{app}\installer\scripts\setup-station.ps1"""; WorkingDir: "{app}"
Name: "{autodesktop}\{#AppName}"; Filename: "{app}\SmartWeighbridge.bat"; WorkingDir: "{app}"; Tasks: desktopicon

[Run]
Filename: "powershell.exe"; Parameters: "-ExecutionPolicy Bypass -File ""{app}\installer\scripts\setup-station.ps1"""; Description: "Run first-time database setup"; Flags: postinstall skipifsilent unchecked
Filename: "notepad.exe"; Parameters: "{app}\.env"; Description: "Edit .env configuration"; Flags: postinstall skipifsilent unchecked
Filename: "{app}\SmartWeighbridge.bat"; Description: "Launch {#AppName}"; Flags: postinstall skipifsilent nowait unchecked

[UninstallRun]
Filename: "{app}\Stop SmartWeighbridge.bat"; Flags: runhidden

[Code]
procedure CurStepChanged(CurStep: TSetupStep);
var
  EnvPath, TemplatePath, EnvContent: String;
begin
  if CurStep = ssPostInstall then
  begin
    EnvPath := ExpandConstant('{app}\.env');
    TemplatePath := ExpandConstant('{app}\installer\env\.env.station.example');
    if not FileExists(EnvPath) and FileExists(TemplatePath) then
    begin
      if LoadStringFromFile(TemplatePath, EnvContent) then
      begin
        StringChangeEx(EnvContent, 'DB_CLOUD_SSL_CA=', 'DB_CLOUD_SSL_CA=' + ExpandConstant('{app}\storage\certs\ca-certificate.crt'), True);
        SaveStringToFile(EnvPath, EnvContent, False);
      end;
    end;
  end;
end;
