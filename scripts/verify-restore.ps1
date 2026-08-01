[CmdletBinding(SupportsShouldProcess = $true)]
param(
    [Parameter(Mandatory = $true)]
    [ValidateScript({ Test-Path -LiteralPath $_ -PathType Leaf })]
    [string] $BackupFile,

    [Parameter(Mandatory = $true)]
    [ValidateScript({ Test-Path -LiteralPath $_ -PathType Leaf })]
    [string] $DefaultsExtraFile,

    [Parameter(Mandatory = $true)]
    [ValidateScript({ Test-Path -LiteralPath $_ -PathType Leaf })]
    [string] $AgeIdentityFile,

    [string] $ChecksumFile,

    [ValidatePattern('^[A-Za-z0-9_]+$')]
    [string] $RestoreDatabase,

    [string] $DatabaseHost = '127.0.0.1',

    [ValidateRange(1, 65535)]
    [int] $Port = 3306,

    [string] $MariaDbClientPath = 'mariadb',

    [string] $AgePath = 'age',

    [switch] $KeepDatabase
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Invoke-CheckedProcess {
    param(
        [Parameter(Mandatory = $true)]
        [string] $FilePath,

        [Parameter(Mandatory = $true)]
        [string[]] $ArgumentList,

        [string] $StandardInput,

        [string] $StandardOutput,

        [Parameter(Mandatory = $true)]
        [string] $StandardError
    )

    $parameters = @{
        FilePath              = $FilePath
        ArgumentList          = $ArgumentList
        RedirectStandardError = $StandardError
        PassThru              = $true
        Wait                  = $true
        WindowStyle           = 'Hidden'
    }

    if ($StandardInput) {
        $parameters.RedirectStandardInput = $StandardInput
    }

    if ($StandardOutput) {
        $parameters.RedirectStandardOutput = $StandardOutput
    }

    $process = Start-Process @parameters

    if ($process.ExitCode -ne 0) {
        $details = if (Test-Path -LiteralPath $StandardError) {
            Get-Content -LiteralPath $StandardError -Raw
        } else {
            'Tidak ada output error.'
        }

        throw "Perintah $FilePath gagal dengan exit code $($process.ExitCode). $details"
    }
}

$null = Get-Command $MariaDbClientPath -ErrorAction Stop
$null = Get-Command $AgePath -ErrorAction Stop

$resolvedBackup = (Resolve-Path -LiteralPath $BackupFile).Path
$resolvedCredential = (Resolve-Path -LiteralPath $DefaultsExtraFile).Path
$resolvedIdentity = (Resolve-Path -LiteralPath $AgeIdentityFile).Path

if (-not $ChecksumFile) {
    $ChecksumFile = "$resolvedBackup.sha256"
}

$resolvedChecksum = (Resolve-Path -LiteralPath $ChecksumFile).Path

if (-not $RestoreDatabase) {
    $RestoreDatabase = 'medis_restore_' + (Get-Date -Format 'yyyyMMddHHmmss')
}

if ($RestoreDatabase -notmatch '^[A-Za-z0-9_]+$') {
    throw 'RestoreDatabase hanya boleh berisi huruf, angka, dan underscore.'
}

$expectedHash = ((Get-Content -LiteralPath $resolvedChecksum -Raw).Trim() -split '\s+')[0].ToLowerInvariant()
$actualHash = (Get-FileHash -LiteralPath $resolvedBackup -Algorithm SHA256).Hash.ToLowerInvariant()

if ($expectedHash -ne $actualHash) {
    throw "Checksum backup tidak cocok. Expected $expectedHash, actual $actualHash."
}

if (-not $PSCmdlet.ShouldProcess("$DatabaseHost`:$Port/$RestoreDatabase", 'Restore dan verifikasi backup pada database terisolasi')) {
    return
}

$workDirectory = Join-Path ([System.IO.Path]::GetTempPath()) ("clinic-restore-" + [guid]::NewGuid().ToString('N'))
$null = [System.IO.Directory]::CreateDirectory($workDirectory)
$plainPath = Join-Path $workDirectory 'restore.sql'
$errorPath = Join-Path $workDirectory 'process.stderr.log'
$queryOutputPath = Join-Path $workDirectory 'verification.txt'
$databaseCreated = $false

$connectionArguments = @(
    "--defaults-extra-file=`"$resolvedCredential`"",
    "--host=$DatabaseHost",
    "--port=$Port",
    '--protocol=tcp'
)

try {
    Invoke-CheckedProcess -FilePath $AgePath -ArgumentList @('-d', '-i', "`"$resolvedIdentity`"", '-o', "`"$plainPath`"", "`"$resolvedBackup`"") -StandardError $errorPath

    $createSql = "CREATE DATABASE ``$RestoreDatabase`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    Invoke-CheckedProcess -FilePath $MariaDbClientPath -ArgumentList ($connectionArguments + @('--execute', "`"$createSql`"")) -StandardError $errorPath
    $databaseCreated = $true

    Invoke-CheckedProcess -FilePath $MariaDbClientPath -ArgumentList ($connectionArguments + @($RestoreDatabase)) -StandardInput $plainPath -StandardError $errorPath

    $verificationSql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$RestoreDatabase' AND table_type = 'BASE TABLE'; SELECT COUNT(*) FROM information_schema.triggers WHERE trigger_schema = '$RestoreDatabase';"
    Invoke-CheckedProcess -FilePath $MariaDbClientPath -ArgumentList ($connectionArguments + @('--batch', '--skip-column-names', '--execute', "`"$verificationSql`"")) -StandardOutput $queryOutputPath -StandardError $errorPath

    $verificationLines = @(Get-Content -LiteralPath $queryOutputPath | Where-Object { $_ -match '^\d+$' })
    if ($verificationLines.Count -lt 2 -or [int] $verificationLines[0] -lt 1 -or [int] $verificationLines[1] -lt 1) {
        throw 'Restore tidak memiliki tabel atau trigger yang diwajibkan.'
    }

    [PSCustomObject]@{
        BackupFile      = $resolvedBackup
        RestoreDatabase = $RestoreDatabase
        TableCount      = [int] $verificationLines[0]
        TriggerCount    = [int] $verificationLines[1]
        Sha256          = $actualHash
        VerifiedAtWib   = (Get-Date).ToString('yyyy-MM-dd HH:mm:ss zzz')
    }
} finally {
    if ($databaseCreated -and -not $KeepDatabase) {
        $dropSql = "DROP DATABASE ``$RestoreDatabase``"
        Invoke-CheckedProcess -FilePath $MariaDbClientPath -ArgumentList ($connectionArguments + @('--execute', "`"$dropSql`"")) -StandardError $errorPath
    }

    if (Test-Path -LiteralPath $workDirectory) {
        Remove-Item -LiteralPath $workDirectory -Recurse -Force
    }
}
