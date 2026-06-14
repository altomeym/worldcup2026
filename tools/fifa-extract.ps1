# tools/fifa-extract.ps1 — يستخرج إحصائيات تقارير FIFA الرسميّة محليّاً ويبني
#   assets/fifa-stats.json. شغّله بعد لعب مباريات جديدة، ثم زامِن الملف للإنتاج.
#
#   powershell -ExecutionPolicy Bypass -File tools/fifa-extract.ps1
#
# يتطلّب: pdftotext (مرفق مع Git for Windows) + PHP. Hostinger بلا pdftotext → محليّ فقط.

$ErrorActionPreference = 'Stop'
$proj = Split-Path -Parent $PSScriptRoot
$php  = 'C:/xampp/php/php.exe'

$pdftotext = (Get-Command pdftotext.exe -ErrorAction SilentlyContinue).Source
if (-not $pdftotext) { $pdftotext = 'C:/Program Files/Git/mingw64/bin/pdftotext.exe' }
if (-not (Test-Path $pdftotext)) { throw "pdftotext not found — install Git for Windows or poppler." }

$txtDir = Join-Path $PSScriptRoot '_fifatxt'
New-Item -ItemType Directory -Force -Path $txtDir | Out-Null

# 1) خريطة التقارير {رقم: رابط} من صفحة hub الرسميّة
Write-Host 'Fetching FIFA report list...'
$map = (& $php (Join-Path $PSScriptRoot 'fifa-build.php') map) | ConvertFrom-Json

# 2) نزّل كل تقرير واستخرج نصّه بوضع الجدول (-table)
foreach ($n in $map.PSObject.Properties.Name) {
    $url = $map.$n
    $pdf = Join-Path $txtDir "$n.pdf"
    $txt = Join-Path $txtDir "$n.txt"
    try {
        Invoke-WebRequest -Uri $url -OutFile $pdf -UserAgent 'Mozilla/5.0' -TimeoutSec 60
        & $pdftotext -table $pdf $txt
        Remove-Item $pdf -Force
        Write-Host "  extracted M$n"
    } catch { Write-Warning "  skip M${n}: $($_.Exception.Message)" }
}

# 3) ابنِ assets/fifa-stats.json
& $php (Join-Path $PSScriptRoot 'fifa-build.php') build $txtDir

# 4) نظّف الملفّات المؤقّتة
Remove-Item $txtDir -Recurse -Force
Write-Host 'Done. Now sync assets/fifa-stats.json to production.'
