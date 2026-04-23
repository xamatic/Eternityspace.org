param(
    [string]$RemoteHost = "eternityspace.org",
    [string]$LocalRepoPath = ".",
    [string]$RemoteChatPath = "~/public_html/chat",
    [string]$RemoteBackupPath = "~/public_html/chat_backups",
    [string]$RemoteStagePath = "~/public_html/chat_deploy_stage",
    [string]$HealthUrl = "https://eternityspace.org/chat/",
    [int]$KeepBackups = 10,
    [switch]$UseDelete,
    [switch]$Rollback,
    [switch]$ListBackups,
    [string]$RollbackBackup = "latest"
)

$ErrorActionPreference = 'Stop'

function Require-Command {
    param([string]$Name)
    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "Required command '$Name' is not installed or not in PATH."
    }
}

function Resolve-RemoteShellPath {
  param([string]$Path)
  if ([string]::IsNullOrWhiteSpace($Path)) {
    return $Path
  }
  if ($Path -eq "~") {
    return '$HOME'
  }
  if ($Path.StartsWith("~/") -or $Path.StartsWith("~\\")) {
    return '$HOME/' + $Path.Substring(2).Replace('\\', '/')
  }
  return $Path.Replace('\\', '/')
}

  $mode = "deploy"
  if ($ListBackups -and $Rollback) {
    throw "Use either -ListBackups or -Rollback, not both."
  }
  if ($ListBackups) {
    $mode = "list"
  }
  elseif ($Rollback) {
    $mode = "rollback"
  }

Require-Command "ssh"
Require-Command "scp"
  if ($mode -eq "deploy") {
    Require-Command "tar"
  }

  $resolvedRepo = $null
  if ($mode -eq "deploy") {
    $resolvedRepo = (Resolve-Path $LocalRepoPath).Path
    if (-not (Test-Path (Join-Path $resolvedRepo ".git"))) {
      throw "LocalRepoPath must point to a git repository root."
    }
}

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$archiveName = "chat_release_$timestamp.tar.gz"
  $localArchive = $null
  if ($mode -eq "deploy") {
    $localArchive = Join-Path ([System.IO.Path]::GetTempPath()) $archiveName
  }
$localRunner = Join-Path ([System.IO.Path]::GetTempPath()) "chat_deploy_runner_$timestamp.sh"

try {
    if ($mode -eq "deploy") {
      Write-Host "[1/5] Creating release archive from local repository..."
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
MODE="${8:-deploy}"
ROLLBACK_SELECTOR="${9:-latest}"

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

mkdir -p "$REMOTE_BACKUP_PATH" "$REMOTE_STAGE_PATH"

if [ ! -d "$REMOTE_CHAT_PATH" ]; then
  echo "ERROR: Remote chat path not found: $REMOTE_CHAT_PATH" >&2
  exit 1
fi

if [ "$MODE" = "list" ]; then
  ls -1t "$REMOTE_BACKUP_PATH"/chat_full_*.tar.gz 2>/dev/null || true
  exit 0
fi

select_backup_file() {
  local selector="$1"
  local candidate=""
  if [ -z "$selector" ] || [ "$selector" = "latest" ]; then
    candidate="$(ls -1t "$REMOTE_BACKUP_PATH"/chat_full_*.tar.gz 2>/dev/null | head -n 1 || true)"
  elif [ "$selector" = "previous" ]; then
    candidate="$(ls -1t "$REMOTE_BACKUP_PATH"/chat_full_*.tar.gz 2>/dev/null | sed -n '2p' || true)"
  elif [ -f "$selector" ]; then
    candidate="$selector"
  elif [ -f "$REMOTE_BACKUP_PATH/$selector" ]; then
    candidate="$REMOTE_BACKUP_PATH/$selector"
  else
    candidate="$(ls -1t "$REMOTE_BACKUP_PATH"/chat_full_*"$selector"*.tar.gz 2>/dev/null | head -n 1 || true)"
  fi
  echo "$candidate"
}

TS=$(date +%Y%m%d_%H%M%S)
PARENT_DIR=$(dirname "$REMOTE_CHAT_PATH")
CHAT_DIR=$(basename "$REMOTE_CHAT_PATH")

if [ "$MODE" = "rollback" ]; then
  ROLLBACK_FILE="$(select_backup_file "$ROLLBACK_SELECTOR")"
  if [ -z "$ROLLBACK_FILE" ] || [ ! -f "$ROLLBACK_FILE" ]; then
    echo "ERROR: Could not find rollback backup for selector: $ROLLBACK_SELECTOR" >&2
    exit 1
  fi

  SAFETY_FILE="$REMOTE_BACKUP_PATH/chat_pre_rollback_$TS.tar.gz"
  tar -czf "$SAFETY_FILE" -C "$PARENT_DIR" "$CHAT_DIR"

  rm -rf "$REMOTE_CHAT_PATH"
  tar -xzf "$ROLLBACK_FILE" -C "$PARENT_DIR"

  HEALTH_CODE="NA"
  if [ -n "$HEALTH_URL" ]; then
    HEALTH_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$HEALTH_URL" || true)
  fi

  echo "ROLLBACK_FILE=$ROLLBACK_FILE"
  echo "SAFETY_BACKUP_FILE=$SAFETY_FILE"
  echo "HEALTH_CODE=$HEALTH_CODE"
  echo "ROLLBACK_DONE=1"
  exit 0
fi

if [ "$MODE" != "deploy" ]; then
  echo "ERROR: Unknown mode: $MODE" >&2
  exit 1
fi

if ! command -v rsync >/dev/null 2>&1; then
  echo "ERROR: rsync is required on the remote server." >&2
  exit 1
fi

if [ -z "$ARCHIVE_NAME" ]; then
  echo "ERROR: Missing archive name for deploy mode." >&2
  exit 1
fi

BACKUP_FILE="$REMOTE_BACKUP_PATH/chat_full_$TS.tar.gz"

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
  --exclude ".system/theme_public" \
  --exclude ".system/upload/theme_public" \
  --exclude "compare_results.txt" \
  --exclude "css/themes" \
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

    Write-Host "[2/5] Uploading remote deploy runner..."
    & scp $localRunner "$RemoteHost`:~/chat_deploy_runner.sh"
    if ($LASTEXITCODE -ne 0) {
        throw "Failed uploading remote deploy runner script."
    }

    Write-Host "[3/5] Ensuring remote backup/stage directories exist..."
    $remoteBackupShellPath = Resolve-RemoteShellPath $RemoteBackupPath
    $remoteStageShellPath = Resolve-RemoteShellPath $RemoteStagePath
    $remotePrepareCommand = "bash -lc 'mkdir -p `"$remoteBackupShellPath`" `"$remoteStageShellPath`"'"
    & ssh $RemoteHost $remotePrepareCommand
    if ($LASTEXITCODE -ne 0) {
      throw "Failed preparing remote backup/stage directories. Command: $remotePrepareCommand"
    }

    if ($mode -eq "deploy") {
      Write-Host "[4/5] Uploading release archive to remote stage..."
      & scp $localArchive "$RemoteHost`:$RemoteStagePath/$archiveName"
      if ($LASTEXITCODE -ne 0) {
        throw "Failed uploading release archive."
      }
    }

    $deleteFlag = if ($UseDelete.IsPresent) { "1" } else { "0" }
    $archiveArg = if ($mode -eq "deploy") { $archiveName } else { "" }
    $rollbackArg = if ([string]::IsNullOrWhiteSpace($RollbackBackup)) { "latest" } else { $RollbackBackup }

    if ($mode -eq "deploy") {
      Write-Host "[5/5] Running remote backup + deploy sequence..."
    }
    elseif ($mode -eq "rollback") {
      Write-Host "[4/5] Running remote rollback sequence..."
    }
    else {
      Write-Host "[4/5] Listing remote backups..."
    }

    & ssh $RemoteHost "bash ~/chat_deploy_runner.sh '$RemoteChatPath' '$RemoteBackupPath' '$RemoteStagePath' '$archiveArg' '$KeepBackups' '$deleteFlag' '$HealthUrl' '$mode' '$rollbackArg'"
    if ($LASTEXITCODE -ne 0) {
      if ($mode -eq "rollback") {
        throw "Remote rollback failed."
      }
      elseif ($mode -eq "list") {
        throw "Remote backup listing failed."
      }
      throw "Remote deployment failed."
    }

    Write-Host "[5/5] Cleaning remote runner..."
    & ssh $RemoteHost "rm -f ~/chat_deploy_runner.sh"

    if ($mode -eq "deploy") {
      Write-Host "[5/5] Deployment finished successfully."
    }
    elseif ($mode -eq "rollback") {
      Write-Host "[5/5] Rollback finished successfully."
    }
    else {
      Write-Host "[5/5] Backup list completed."
    }
    Write-Host "Done."
}
finally {
    if ($localArchive -and (Test-Path $localArchive)) { Remove-Item $localArchive -Force }
    if (Test-Path $localRunner) { Remove-Item $localRunner -Force }
}
