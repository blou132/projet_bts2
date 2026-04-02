Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$RootDir = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$EnvFile = Join-Path $RootDir '.env'
$EnvExample = Join-Path $RootDir '.env.example'
$SqliteFile = Join-Path $RootDir 'database\database.sqlite'

function Update-EnvVar {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Key,
        [Parameter(Mandatory = $true)]
        [string]$Value
    )

    $lines = Get-Content -Path $EnvFile
    $pattern = "^$([regex]::Escape($Key))="
    $replacement = "$Key=$Value"
    $updated = $false

    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match $pattern) {
            $lines[$i] = $replacement
            $updated = $true
            break
        }
    }

    if (-not $updated) {
        $lines += $replacement
    }

    Set-Content -Path $EnvFile -Value $lines
}

function Resolve-PhpExecutable {
    $phpCmd = Get-Command php -ErrorAction SilentlyContinue
    if ($null -ne $phpCmd) {
        return $phpCmd.Source
    }

    $xamppPhp = 'C:\xampp\php\php.exe'
    if (Test-Path $xamppPhp) {
        return $xamppPhp
    }

    throw "PHP introuvable. Installe PHP ou utilise XAMPP (C:\xampp\php\php.exe)."
}

function Invoke-ComposerInstall {
    param(
        [Parameter(Mandatory = $true)]
        [string]$PhpExe
    )

    $composerCmd = Get-Command composer -ErrorAction SilentlyContinue
    if ($null -ne $composerCmd) {
        & $composerCmd.Source install --no-interaction --prefer-dist
        return
    }

    $localComposer = Join-Path $RootDir 'composer'
    if (Test-Path $localComposer) {
        & $PhpExe $localComposer install --no-interaction --prefer-dist
        return
    }

    throw "Composer introuvable. Installe Composer globalement ou conserve le binaire 'composer' a la racine du projet."
}

Write-Host "[1/5] Preparation de .env..."
if (-not (Test-Path $EnvFile)) {
    Copy-Item -Path $EnvExample -Destination $EnvFile
}

New-Item -Path (Join-Path $RootDir 'database') -ItemType Directory -Force | Out-Null
if (-not (Test-Path $SqliteFile)) {
    New-Item -Path $SqliteFile -ItemType File -Force | Out-Null
}

$sqliteEnvPath = $SqliteFile.Replace('\', '/')

Update-EnvVar -Key 'DB_CONNECTION' -Value 'sqlite'
Update-EnvVar -Key 'DB_DATABASE' -Value $sqliteEnvPath
Update-EnvVar -Key 'SESSION_DRIVER' -Value 'database'
Update-EnvVar -Key 'CACHE_STORE' -Value 'database'
Update-EnvVar -Key 'QUEUE_CONNECTION' -Value 'database'
Update-EnvVar -Key 'ADMIN_USERNAME' -Value 'admin'
Update-EnvVar -Key 'ADMIN_PASSWORD' -Value 'admin123'
Update-EnvVar -Key 'ADMIN_SYSTEM_EMAIL' -Value 'admin-system@example.test'
Update-EnvVar -Key 'JMI_USERNAME' -Value 'client'
Update-EnvVar -Key 'JMI_PASSWORD' -Value 'client123'
Update-EnvVar -Key 'JMI_SYSTEM_EMAIL' -Value 'support-system@example.test'
Update-EnvVar -Key 'JMI_DISPLAY_NAME' -Value '"Support Demo"'
Update-EnvVar -Key 'SITE_OWNER_FULLNAME' -Value '"Jean Exemple"'
Update-EnvVar -Key 'SITE_CONTACT_EMAIL' -Value 'contact@example.test'
Update-EnvVar -Key 'SITE_HOSTING_PROVIDER' -Value '"Hebergeur Demo SAS"'
Update-EnvVar -Key 'SITE_HOSTING_ADDRESS' -Value '"1 Rue des Serveurs, 75001 Paris"'
Update-EnvVar -Key 'SITE_HOSTING_PHONE' -Value '"01 11 22 33 44"'

$phpExe = Resolve-PhpExecutable

Write-Host "[2/5] Installation des dependances PHP..."
if (-not (Test-Path (Join-Path $RootDir 'vendor'))) {
    Invoke-ComposerInstall -PhpExe $phpExe
}

Write-Host "[3/5] Nettoyage cache Laravel..."
& $phpExe (Join-Path $RootDir 'artisan') optimize:clear | Out-Null

Write-Host "[4/5] Initialisation base (migrate:fresh --seed)..."
& $phpExe (Join-Path $RootDir 'artisan') key:generate --force | Out-Null
& $phpExe (Join-Path $RootDir 'artisan') migrate:fresh --seed --force

Write-Host "[5/5] Termine."
Write-Host ''
Write-Host 'Comptes de demonstration:'
Write-Host '- Admin: identifiant=admin / mot de passe=admin123'
Write-Host '- JMI (tickets/messages clients): identifiant=client / mot de passe=client123'
Write-Host '- Utilisateur: email=user@gmail.com / mot de passe=123456789'
Write-Host '- Donnees fictives: 9 demandes + discussions deja pre-remplies'
Write-Host ''
Write-Host 'Lancement:'
Write-Host '- php artisan serve'
Write-Host '- Ouvrir http://127.0.0.1:8000'
