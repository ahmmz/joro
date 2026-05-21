#!/bin/sh
set -e

# Default bind to 0.0.0.0 so container ports are accessible externally
BIND_ADDR=${JORO_BIND:-"0.0.0.0"}
FLAGS="--bind=${BIND_ADDR}"

# Handle basic server ports
if [ -n "$JORO_PROXY_PORT" ]; then
    FLAGS="$FLAGS --proxy-port=$JORO_PROXY_PORT"
fi

if [ -n "$JORO_UI_PORT" ]; then
    FLAGS="$FLAGS --ui-port=$JORO_UI_PORT"
fi

# Default data directory to /data for persistence
DATA_DIR=${JORO_DATA_DIR:-"/data"}
FLAGS="$FLAGS --data-dir=$DATA_DIR"

# Developer features
if [ "$JORO_DEV" = "true" ]; then
    FLAGS="$FLAGS --dev"
fi

if [ -n "$JORO_VITE_URL" ]; then
    FLAGS="$FLAGS --vite-url=$JORO_VITE_URL"
fi

# Listener Mode settings
if [ "$JORO_LISTENER" = "true" ]; then
    FLAGS="$FLAGS --listener"
fi

# Listener Mode port defaults (unprivileged inside container)
DNS_PORT=${JORO_DNS_PORT:-"1053"}
FLAGS="$FLAGS --dns-port=$DNS_PORT"

HTTP_PORT=${JORO_HTTP_PORT:-"1080"}
FLAGS="$FLAGS --http-port=$HTTP_PORT"

HTTPS_PORT=${JORO_HTTPS_PORT:-"1443"}
FLAGS="$FLAGS --https-port=$HTTPS_PORT"

SMTP_PORT=${JORO_SMTP_PORT:-"1025"}
FLAGS="$FLAGS --smtp-port=$SMTP_PORT"

SMTPS_PORT=${JORO_SMTPS_PORT:-"1465"}
FLAGS="$FLAGS --smtps-port=$SMTPS_PORT"

if [ -n "$JORO_TLS_CERT" ]; then
    FLAGS="$FLAGS --tls-cert=$JORO_TLS_CERT"
fi

if [ -n "$JORO_TLS_KEY" ]; then
    FLAGS="$FLAGS --tls-key=$JORO_TLS_KEY"
fi

if [ -n "$JORO_COLLABORATOR_DOMAIN" ]; then
    FLAGS="$FLAGS --domain=$JORO_COLLABORATOR_DOMAIN"
fi

if [ -n "$JORO_RESPONSE_IP" ]; then
    FLAGS="$FLAGS --response-ip=$JORO_RESPONSE_IP"
fi

# Team Server features (requires listener mode)
if [ "$JORO_TEAMSERVER" = "true" ]; then
    FLAGS="$FLAGS --teamserver"
fi

# Background and updater checks
if [ "$JORO_DISABLE_UPDATE_CHECKS" = "true" ]; then
    FLAGS="$FLAGS --disable-update-checks"
fi

# --- Non-Root Privilege Dropping (PUID / PGID) ---
# Check if running as root (UID 0) to perform privilege dropping
if [ "$(id -u)" = "0" ]; then
    # Default PUID/PGID to 1000 if not specified
    USER_ID=${PUID:-1000}
    GROUP_ID=${PGID:-1000}

    echo "Running as root. Dropping privileges to PUID=$USER_ID and PGID=$GROUP_ID..."

    # Retrieve or create group using awk (POSIX compliant, no getent needed)
    group_name=$(awk -F: -v gid="$GROUP_ID" '$3 == gid {print $1; exit}' /etc/group)
    if [ -z "$group_name" ]; then
        group_name="joro-group"
        addgroup -g "$GROUP_ID" "$group_name"
    fi

    # Retrieve or create user using awk (POSIX compliant, no getent needed)
    user_name=$(awk -F: -v uid="$USER_ID" '$3 == uid {print $1; exit}' /etc/passwd)
    if [ -z "$user_name" ]; then
        user_name="joro-user"
        adduser -u "$USER_ID" -G "$group_name" -h "$DATA_DIR" -s /bin/sh -D "$user_name"
    fi

    # Ensure ownership of the data directory is correct for the user
    chown -R "$user_name":"$group_name" "$DATA_DIR"

    # Start Joro as the non-root user using su-exec
    echo "Starting Joro with flags: $FLAGS"
    exec su-exec "$user_name":"$group_name" /app/joro $FLAGS "$@"
else
    # Already running as a non-root user (e.g. via --user in Docker/Kubernetes)
    echo "Running as non-root user ($(id -un)). Direct execution..."
    echo "Starting Joro with flags: $FLAGS"
    exec /app/joro $FLAGS "$@"
fi
