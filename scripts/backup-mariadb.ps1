[CmdletBinding(SupportsShouldProcess = $true)]
param(
    [Parameter(Mandatory = $true)]
    [ValidateScript({ Test-Path -LiteralPath $_ -PathType Leaf })]
    [string] $DefaultsExtraFile,

    [Parameter(Mandatory = $true)]
    [ValidateNotNullOrEmpty()]
    [string] $AgeRecipient,

    [ValidatePattern('^[A-Za-z0-9_]+$')]
    [string] $Database = 'medis',

    [string] $DatabaseHost = '127.0.0.1',

    [ValidateRange(1, 65535)]
    [int] $Port = 3306,

    [string] $BackupDirectory = (Join-Path $PSScriptRoot '..\storage\app\backups'),

    [ValidateRange(7, 3650)]
    [int] $RetentionDays = 35,

    [string] $MariaDbDumpPath = 'mariadb-dump',

    [string] $AgePath = 'age'
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
        FilePath               = $FilePath
        ArgumentList           = $ArgumentList
        RedirectStandardError  = $StandardError
        PassThru               = $true
        Wait                   = $true
        WindowStyle            = 'Hidden'
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

$null = Get-Command $MariaDbDumpPath -ErrorAction Stop
$null = Get-Command $AgePath -ErrorAction Stop

$credentialPath = (Resolve-Path -LiteralPath $DefaultsExtraFile).Path
$backupRoot = [System.IO.Path]::GetFullPath($BackupDirectory)
$null = [System.IO.Directory]::CreateDirectory($backupRoot)

$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$baseName = "$Database-$timestamp"
$plainPath = Join-Path $backupRoot "$baseName.sql.part"
$encryptedPartPath = Join-Path $backupRoot "$baseName.sql.age.part"
$encryptedPath = Join-Path $backupRoot "$baseName.sql.age"
$checksumPath = "$encryptedPath.sha256"
$errorPath = Join-Path $backupRoot "$baseName.stderr.log"

if (-not $PSCmdlet.ShouldProcess("$DatabaseHost`:$Port/$Database", "Membuat backup terenkripsi $encryptedPath")) {
    return
}

try {
    $dumpArguments = @(
        "--defaults-extra-file=`"$credentialPath`"",
        "--host=$DatabaseHost",
        "--port=$Port",
        '--protocol=tcp',
        '--single-transaction',
        '--quick',
        '--routines',
        '--events',
        '--triggers',
        '--hex-blob',
        '--default-character-set=utf8mb4',
        $Database
    )

    Invoke-CheckedProcess -FilePath $MariaDbDumpPath -ArgumentList $dumpArguments -StandardOutput $plainPath -StandardError $errorPath

    $ageArguments = @(
        '-r',
        $AgeRecipient,
        '-o',
        "`"$encryptedPartPath`"",
        "`"$plainPath`""
    )

    Invoke-CheckedProcess -FilePath $AgePath -ArgumentList $ageArguments -StandardError $errorPath

    Move-Item -LiteralPath $encryptedPartPath -Destination $encryptedPath
    $hash = (Get-FileHash -LiteralPath $encryptedPath -Algorithm SHA256).Hash.ToLowerInvariant()
    Set-Content -LiteralPath $checksumPath -Value "$hash  $([System.IO.Path]::GetFileName($encryptedPath))" -Encoding ascii

    $cutoff = (Get-Date).AddDays(-$RetentionDays)
    Get-ChildItem -LiteralPath $backupRoot -File |
        Where-Object {
            $_.Name -like "$Database-*.sql.age*" -and
            $_.Name -notlike '*.part' -and
            $_.LastWriteTime -lt $cutoff
        } |
        ForEach-Object {
            if ($PSCmdlet.ShouldProcess($_.FullName, "Menghapus backup yang lebih lama dari $RetentionDays hari")) {
                Remove-Item -LiteralPath $_.FullName -Force
            }
        }

    [PSCustomObject]@{
        BackupFile  = $encryptedPath
        ChecksumFile = $checksumPath
        Sha256      = $hash
        CreatedAtWib = (Get-Date).ToString('yyyy-MM-dd HH:mm:ss zzz')
    }
} finally {
    foreach ($temporaryPath in @($plainPath, $encryptedPartPath, $errorPath)) {
        if (Test-Path -LiteralPath $temporaryPath) {
            Remove-Item -LiteralPath $temporaryPath -Force
        }
    }
}
