# Stage 1: Build the React frontend
FROM node:20-alpine AS frontend-builder
WORKDIR /app/web
COPY web/package*.json ./
RUN npm ci || npm install
COPY web/ ./
RUN npm run build

# Stage 2: Build the Go application
FROM golang:alpine AS go-builder
ARG COMMIT=docker
WORKDIR /app

# Install compilation tools required for CGO (Go plugins require CGO_ENABLED=1)
RUN apk add --no-cache git build-base musl-dev gcc

# Copy the local dependency (sdk) and module manifests
COPY sdk/ ./sdk/
COPY go.mod go.sum ./
RUN go mod download

# Copy the rest of the application files
COPY . .

# Embed the pre-built frontend into the Go build path
COPY --from=frontend-builder /app/web/dist ./web/dist

# Build the final static-ish binary with plugin support enabled (CGO_ENABLED=1)
RUN CGO_ENABLED=1 GOOS=linux \
    go build -tags="netgo,osusergo" -ldflags="-s -w -X main.commit=${COMMIT}" -o joro .

# Stage 3: Minimal, clean runtime container
FROM alpine:latest
WORKDIR /app

# Install CA certificates and su-exec (for dropping privileges to a custom PUID/PGID)
RUN apk add --no-cache ca-certificates su-exec

# Expose default Joro ports:
# - 8080: Intercepting Proxy Port
# - 9090: UI/API Control Port
EXPOSE 8080 9090

# Expose unprivileged Listener/Team Server ports (mapped via Compose to host standard ports):
# - 1053/udp: Callback DNS
# - 1080: Callback HTTP
# - 1443: Callback HTTPS
# - 1025: Callback SMTP
# - 1465: Callback SMTPS
EXPOSE 1053/udp 1080 1443 1025 1465

# Create persistent storage folder for CA certs, SQLite DBs, and custom plugins
RUN mkdir -p /data

# Create a standard non-root system user and group (UID/GID 1000)
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} -S joro && \
    adduser -u ${UID} -S -G joro -h /data -s /bin/sh -D joro

# Copy the compiled binary and entrypoint script
COPY --from=go-builder /app/joro /app/joro
COPY entrypoint.sh /app/entrypoint.sh

# Ensure ownership of the application directory and data directory
RUN chown -R joro:joro /app /data

# Use entrypoint script to parse JORO_* env variables at startup
ENTRYPOINT ["/app/entrypoint.sh"]
