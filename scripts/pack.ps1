# Pack a deploy ZIP (excludes git, docs, cursor). Extract at the domain document root.

$root = Split-Path -Parent $PSScriptRoot
$out = Join-Path $root "turbo-pbn-deploy.zip"
if (Test-Path $out) { Remove-Item $out -Force }

$exclude = @('.git', '.cursor', 'docs', '*.zip')
$items = Get-ChildItem -Force $root | Where-Object {
    $_.Name -notin @('.git', '.cursor', 'docs') -and $_.Name -notlike '*.zip'
}

Compress-Archive -Path $items.FullName -DestinationPath $out -Force
Write-Host "Wrote $out"
