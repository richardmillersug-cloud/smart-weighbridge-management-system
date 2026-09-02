# Convert installer/assets/app-icon.png to app-icon.ico for Windows shortcuts.
param(
    [string]$SourcePng = "",
    [string]$OutputIco = ""
)

$ErrorActionPreference = "Stop"
$AssetsDir = Join-Path $PSScriptRoot "..\assets"
if (-not $SourcePng) { $SourcePng = Join-Path $AssetsDir "app-icon.png" }
if (-not $OutputIco) { $OutputIco = Join-Path $AssetsDir "app-icon.ico" }

Add-Type -AssemblyName System.Drawing

$sizes = @(256, 128, 64, 48, 32, 16)
$png = [System.Drawing.Image]::FromFile((Resolve-Path $SourcePng))

try {
    $memoryStream = New-Object System.IO.MemoryStream
    $writer = New-Object System.IO.BinaryWriter($memoryStream)

    $writer.Write([int16]0)
    $writer.Write([int16]1)
    $writer.Write([int16]$sizes.Count)

    $offset = 6 + (16 * $sizes.Count)
    $imageDataList = New-Object System.Collections.Generic.List[byte[]]

    foreach ($size in $sizes) {
        $bitmap = New-Object System.Drawing.Bitmap $size, $size
        $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
        $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
        $graphics.DrawImage($png, 0, 0, $size, $size)
        $graphics.Dispose()

        $ms = New-Object System.IO.MemoryStream
        $bitmap.Save($ms, [System.Drawing.Imaging.ImageFormat]::Png)
        $bytes = $ms.ToArray()
        $ms.Dispose()
        $bitmap.Dispose()

        $imageDataList.Add($bytes) | Out-Null

        $widthByte = if ($size -ge 256) { [byte]0 } else { [byte]$size }
        $heightByte = if ($size -ge 256) { [byte]0 } else { [byte]$size }

        $writer.Write($widthByte)
        $writer.Write($heightByte)
        $writer.Write([byte]0)
        $writer.Write([byte]0)
        $writer.Write([int16]0)
        $writer.Write([int16]32)
        $writer.Write([int32]$bytes.Length)
        $writer.Write([int32]$offset)

        $offset += $bytes.Length
    }

    foreach ($data in $imageDataList) {
        $writer.Write($data)
    }

    [System.IO.File]::WriteAllBytes($OutputIco, $memoryStream.ToArray())
    Write-Host "Created: $OutputIco"
}
finally {
    $png.Dispose()
}
