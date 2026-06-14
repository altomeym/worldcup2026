# fifa-auto.ps1 - Auto-extract NEW FIFA official match reports + sync to production.
# ASCII-only (safe for Task Scheduler). Incremental: only downloads reports not yet
# extracted, and only syncs (git push) when something new was built.
# Requires the PC to be on. Hostinger has no pdftotext, so extraction must run here.
$ErrorActionPreference = 'Continue'
$php  = 'C:/xampp/php/php.exe'
$pt   = (Get-Command pdftotext.exe -ErrorAction SilentlyContinue).Source
if (-not $pt) { $pt = 'C:/Program Files/Git/mingw64/bin/pdftotext.exe' }
$proj = 'C:/xampp/htdocs/worldcup2026'
$sync = 'C:/xampp/htdocs/sync-worldcup-to-oss.ps1'
if (-not (Test-Path $pt)) { Write-Output 'pdftotext missing'; exit 1 }

# Only the reports that are NOT yet on the site (NoteProperty = real JSON keys only).
$pending = & $php "$proj/tools/fifa-build.php" pending | ConvertFrom-Json
$names = @($pending.PSObject.Properties | Where-Object { $_.MemberType -eq 'NoteProperty' } | Select-Object -ExpandProperty Name)
if ($names.Count -eq 0) { Write-Output 'no new reports'; exit 0 }

$txtDir = Join-Path $proj 'tools/_fifatxt'
New-Item -ItemType Directory -Force -Path $txtDir | Out-Null
foreach ($n in $names) {
    $url = $pending.$n
    $pdf = Join-Path $txtDir "$n.pdf"
    $txt = Join-Path $txtDir "$n.txt"
    try {
        Invoke-WebRequest -Uri $url -OutFile $pdf -UserAgent 'Mozilla/5.0' -TimeoutSec 60
        & $pt -table $pdf $txt
        Remove-Item $pdf -Force
    } catch { Write-Output ("skip M" + $n) }
}
$built = & $php "$proj/tools/fifa-build.php" build $txtDir
Remove-Item $txtDir -Recurse -Force
Write-Output $built

if ($built -match 'built\s+([1-9]\d*)') {
    & powershell -ExecutionPolicy Bypass -File $sync -Message 'data: auto-update FIFA official match stats'
    Write-Output 'synced'
} else {
    Write-Output 'nothing new built'
}
