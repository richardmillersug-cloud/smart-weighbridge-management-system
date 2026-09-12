!macro customInstall
  DetailPrint "Unblocking Chromium ICU data"
  nsExec::ExecToLog 'powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-ChildItem -LiteralPath ''$INSTDIR'' -Filter icudtl.dat -ErrorAction SilentlyContinue | Unblock-File"'
  DetailPrint "Creating desktop shortcut that starts with --icu-data-dir"
  CreateShortCut "$DESKTOP\Smart Weighbridge.lnk" "$INSTDIR\Start Smart Weighbridge.cmd" "" "$INSTDIR\${APP_EXECUTABLE_FILENAME}" 0
!macroend
