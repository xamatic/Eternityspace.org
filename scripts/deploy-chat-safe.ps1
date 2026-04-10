param(
  [string]$RemoteHost = "eternityspace.org",
    [string]$LocalRepoPath = ".",
    [string]$RemoteChatPath = "~/public_html/chat",
    [string]$RemoteBackupPath = "~/public_html/chat_backups",
    [string]$RemoteStagePath = "~/public_html/chat_deploy_stage",
    [string]$HealthUrl = "https://eternityspace.org/chat/",
    [int]$KeepBackups = 10,
    [switch]$UseDelete
)

$ErrorActionPreference = 'Stop'

function Require-Command {
    param([string]$Name)
    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "Required command '$Name' is not installed or not in PATH."
    }
}

Require-Command "ssh"
Require-Command "scp"
Require-Command "tar"

$resolvedRepo = (Resolve-Path $LocalRepoPath).Path
if (-not (Test-Path (Join-Path $resolvedRepo ".git"))) {
    throw "LocalRepoPath must point to a git repository root."
}

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$archiveName = "chat_release_$timestamp.tar.gz"
$localArchive = Join-Path ([System.IO.Path]::GetTempPath()) $archiveName
$localRunner = Join-Path ([System.IO.Path]::GetTempPath()) "chat_deploy_runner_$timestamp.sh"

try {
    Write-Host "[1/6] Creating release archive from local repository..."
    Push-Location $resolvedRepo
    try {
        if (Test-Path $localArchive) { Remove-Item $localArchive -Force }
        & tar -czf $localArchive --exclude=.git --exclude=.github --exclude=.vscode --exclude=.idea --exclude=node_modules .
        if ($LASTEXITCODE -ne 0) {
            throw "Failed creating local release archive."
        }
    }
    finally {
        Pop-Location
    }

    $remoteRunner = @'
#!/usr/bin/env bash
set -euo pipefail

REMOTE_CHAT_PATH="$1"
REMOTE_BACKUP_PATH="$2"
REMOTE_STAGE_PATH="$3"
ARCHIVE_NAME="$4"
KEEP_BACKUPS="$5"
USE_DELETE="$6"
HEALTH_URL="$7"

expand_home_path() {
  case "$1" in
    ~*) echo "$HOME${1#\~}" ;;
    *) echo "$1" ;;
  esac
}

REMOTE_CHAT_PATH="$(expand_home_path "$REMOTE_CHAT_PATH")"
REMOTE_BACKUP_PATH="$(expand_home_path "$REMOTE_BACKUP_PATH")"
REMOTE_STAGE_PATH="$(expand_home_path "$REMOTE_STAGE_PATH")"

ARCHIVE_PATH="$REMOTE_STAGE_PATH/$ARCHIVE_NAME"
EXTRACT_PATH="$REMOTE_STAGE_PATH/src_${ARCHIVE_NAME%.tar.gz}"

if ! command -v rsync >/dev/null 2>&1; then
  echo "ERROR: rsync is required on the remote server." >&2
  exit 1
fi

mkdir -p "$REMOTE_BACKUP_PATH" "$REMOTE_STAGE_PATH"

if [ ! -d "$REMOTE_CHAT_PATH" ]; then
  echo "ERROR: Remote chat path not found: $REMOTE_CHAT_PATH" >&2
  exit 1
fi

TS=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$REMOTE_BACKUP_PATH/chat_full_$TS.tar.gz"
PARENT_DIR=$(dirname "$REMOTE_CHAT_PATH")
CHAT_DIR=$(basename "$REMOTE_CHAT_PATH")

# Full snapshot backup before any deployment changes.
tar -czf "$BACKUP_FILE" -C "$PARENT_DIR" "$CHAT_DIR"

if [ ! -f "$ARCHIVE_PATH" ]; then
  echo "ERROR: Uploaded archive not found: $ARCHIVE_PATH" >&2
  exit 1
fi

rm -rf "$EXTRACT_PATH"
mkdir -p "$EXTRACT_PATH"
tar -xzf "$ARCHIVE_PATH" -C "$EXTRACT_PATH"

RSYNC_DELETE_ARG=""
if [ "$USE_DELETE" = "1" ]; then
  RSYNC_DELETE_ARG="--delete"
fi

# Deploy code while preserving server-owned/runtime data.
rsync -a $RSYNC_DELETE_ARG \
  --exclude ".git/" \
  --exclude ".github/" \
  --exclude ".vscode/" \
  --exclude "system/database.php" \
  --exclude "system/settings.php" \
  --exclude "avatar/" \
  --exclude "cover/" \
  --exclude "upload/" \
  --exclude "music/" \
  --exclude "room_icon/" \
  --exclude "gift/" \
  --exclude "error_log" \
  --exclude ".ftpquota" \
  --exclude "compare_results.txt" \
  "$EXTRACT_PATH/" "$REMOTE_CHAT_PATH/"

HEALTH_CODE="NA"
if [ -n "$HEALTH_URL" ]; then
  HEALTH_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$HEALTH_URL" || true)
fi

if [ "$KEEP_BACKUPS" -gt 0 ]; then
  ls -1t "$REMOTE_BACKUP_PATH"/chat_full_*.tar.gz 2>/dev/null | tail -n +$((KEEP_BACKUPS + 1)) | xargs -r rm -f
fi

rm -rf "$EXTRACT_PATH" "$ARCHIVE_PATH"

echo "BACKUP_FILE=$BACKUP_FILE"
echo "HEALTH_CODE=$HEALTH_CODE"
echo "DEPLOY_DONE=1"
'@

    $remoteRunnerLf = ($remoteRunner -replace "`r`n", "`n") -replace "`r", "`n"
    [System.IO.File]::WriteAllText($localRunner, $remoteRunnerLf, [System.Text.Encoding]::ASCII)

    Write-Host "[2/6] Uploading remote deploy runner..."
    & scp $localRunner "$RemoteHost`:~/chat_deploy_runner.sh"
    if ($LASTEXITCODE -ne 0) {
        throw "Failed uploading remote deploy runner script."
    }

    Write-Host "[3/6] Ensuring remote backup/stage directories exist..."
    & ssh $RemoteHost "mkdir -p $RemoteBackupPath $RemoteStagePath"
    if ($LASTEXITCODE -ne 0) {
      throw "Failed preparing remote backup/stage directories."
    }

    Write-Host "[4/6] Uploading release archive to remote stage..."
    & scp $localArchive "$RemoteHost`:$RemoteStagePath/$archiveName"
    if ($LASTEXITCODE -ne 0) {
        throw "Failed uploading release archive."
    }

    $deleteFlag = if ($UseDelete.IsPresent) { "1" } else { "0" }

    Write-Host "[5/6] Running remote backup + deploy sequence..."
    & ssh $RemoteHost "bash ~/chat_deploy_runner.sh '$RemoteChatPath' '$RemoteBackupPath' '$RemoteStagePath' '$archiveName' '$KeepBackups' '$deleteFlag' '$HealthUrl'"
    if ($LASTEXITCODE -ne 0) {
        throw "Remote deployment failed."
    }

    Write-Host "[6/6] Cleaning remote runner..."
    & ssh $RemoteHost "rm -f ~/chat_deploy_runner.sh"

    Write-Host "[6/6] Deployment finished successfully."
    Write-Host "Done."
}
finally {
    if (Test-Path $localArchive) { Remove-Item $localArchive -Force }
    if (Test-Path $localRunner) { Remove-Item $localRunner -Force }
}
