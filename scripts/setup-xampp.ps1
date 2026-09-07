# Run this AFTER XAMPP is installed.
# Serves this project at http://localhost:8080 (DocumentRoot = public/)

$ErrorActionPreference = "Stop"

$xamppRoot = $null
foreach ($candidate in @("C:\xampp", "D:\xampp")) {
    if (Test-Path (Join-Path $candidate "apache\bin\httpd.exe")) {
        $xamppRoot = $candidate
        break
    }
}

if (-not $xamppRoot) {
    throw "XAMPP not found at C:\xampp or D:\xampp."
}

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$publicRoot = Join-Path $projectRoot "public"
if (-not (Test-Path $publicRoot)) {
    throw "public/ webroot is missing at $publicRoot"
}

$projectRootApache = ($projectRoot -replace "\\", "/")
$publicRootApache = ($publicRoot -replace "\\", "/")
$httpdConf = Join-Path $xamppRoot "apache\conf\httpd.conf"
$vhostsConf = Join-Path $xamppRoot "apache\conf\extra\httpd-vhosts.conf"
$linkPath = Join-Path $xamppRoot "htdocs\KDesigns-Blooms-and-Styles"
$httpdExe = Join-Path $xamppRoot "apache\bin\httpd.exe"

Write-Host "Using XAMPP at $xamppRoot"
Write-Host "Project root: $projectRoot"
Write-Host "DocumentRoot: $publicRoot"

foreach ($dir in @("storage\logs", "storage\rate_limits", "storage\uploads")) {
    $full = Join-Path $projectRoot $dir
    if (-not (Test-Path $full)) {
        New-Item -ItemType Directory -Path $full -Force | Out-Null
    }
}

if (Test-Path $linkPath) {
    Write-Host "htdocs link already exists: $linkPath"
} else {
    try {
        New-Item -ItemType SymbolicLink -Path $linkPath -Target $publicRoot | Out-Null
        Write-Host "Created htdocs link: $linkPath -> public/"
    } catch {
        Write-Host "Could not create htdocs link (not required). Port 8080 still works."
        Write-Host $_.Exception.Message
    }
}

$httpd = Get-Content $httpdConf -Raw
if ($httpd -notmatch '(?m)^Listen 8080\s*$') {
    $httpd = $httpd -replace '(?m)^Listen 80\s*$', "Listen 80`r`nListen 8080"
    Set-Content -Path $httpdConf -Value $httpd -NoNewline
    Write-Host "Enabled Apache Listen 8080"
}

$httpd = Get-Content $httpdConf -Raw
if ($httpd -match '(?m)^#\s*Include conf/extra/httpd-vhosts.conf') {
    $httpd = $httpd -replace '(?m)^#\s*Include conf/extra/httpd-vhosts.conf', 'Include conf/extra/httpd-vhosts.conf'
    Set-Content -Path $httpdConf -Value $httpd -NoNewline
    Write-Host "Enabled httpd-vhosts.conf"
}

$marker = "# KDesigns Blooms and Styles vhost"
$vhostBlock = @"
$marker
<VirtualHost *:8080>
    DocumentRoot "$publicRootApache"
    ServerName localhost
    ErrorLog "$projectRootApache/storage/logs/apache-error.log"
    CustomLog "$projectRootApache/storage/logs/apache-access.log" common
    <Directory "$publicRootApache">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"@

$vhosts = ""
if (Test-Path $vhostsConf) {
    $vhosts = Get-Content $vhostsConf -Raw
}

if ($vhosts -match [regex]::Escape($marker)) {
    $vhosts = [regex]::Replace(
        $vhosts,
        '(?s)# KDesigns Blooms and Styles vhost\s*<VirtualHost \*:8080>.*?</VirtualHost>',
        $vhostBlock.TrimEnd(),
        1
    )
    Set-Content -Path $vhostsConf -Value $vhosts -NoNewline
    Write-Host "Updated localhost:8080 virtual host DocumentRoot to public/"
} else {
    Add-Content -Path $vhostsConf -Value "`r`n$vhostBlock"
    Write-Host "Added localhost:8080 virtual host (public/)"
}

Write-Host ""
Write-Host "Checking Apache config..."
& $httpdExe -t
if ($LASTEXITCODE -ne 0) {
    throw "Apache config test failed."
}

Write-Host ""
Write-Host "Setup complete."
Write-Host "Restart Apache in XAMPP Control Panel, then open: http://localhost:8080/"
