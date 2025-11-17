# Development Environment

## Quick Start

```bash
# Start development environment
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Stop development environment
docker compose -f docker-compose.yml -f docker-compose.dev.yml down
```

## Services

### Core Services
- **Laravel App** (with Xdebug): http://localhost
- **PostgreSQL**: localhost:5432
- **Redis**: localhost:6379
- **Nginx**: http://localhost (ports 80/443)

### Development Services
- **Vite HMR**: http://localhost:5173
- **Mailpit UI**: http://localhost:8025
- **Mailpit SMTP**: localhost:1025

## Features

### Hot Module Replacement (HMR)
- Vite dev server with HMR enabled
- Changes to Vue/JS/CSS reflected instantly
- WebSocket connection for live updates
- **Wayfinder types auto-generated on startup**

### PHP Debugging (Xdebug)
- Xdebug 3 pre-configured
- Port: 9003
- IDE Key: VSCODE
- Client: host.docker.internal

**VSCode Launch Configuration** (`.vscode/launch.json`):
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}"
      }
    }
  ]
}
```

### Email Testing (Mailpit)
- Web UI: http://localhost:8025
- SMTP: localhost:1025
- All emails caught locally
- No external SMTP needed

## Configuration

### Environment Variables
Copy `.env.dev.example` to `.env`:
```bash
cp .env.dev.example .env
php artisan key:generate
```

### Docker Compose Override
`docker-compose.dev.yml` overrides production config:
- Mounts source code for live editing
- Enables Xdebug
- Adds Vite and Mailpit services
- Sets APP_ENV=local, APP_DEBUG=true

## Development Workflow

### 1. Start Services
```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

### 2. Install Dependencies
```bash
# PHP dependencies (already in container)
docker compose -f docker-compose.yml -f docker-compose.dev.yml exec app composer install

# Node dependencies (Vite handles this)
# Automatically runs on container start

# Wayfinder types (automatically generated on startup)
# Types are generated when app container starts
```

### 3. Run Migrations
```bash
docker compose exec app php artisan migrate
```

### 4. Access Application
- Frontend: http://localhost
- Mailpit: http://localhost:8025
- Vite HMR: http://localhost:5173

### 5. View Logs
```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f app
docker compose logs -f vite
docker compose logs -f mailpit
```

## Debugging

### PHP Debugging
1. Set breakpoint in VSCode
2. Start "Listen for Xdebug" in VSCode
3. Trigger request in browser
4. Debugger pauses at breakpoint

### Queue Debugging
```bash
# Watch queue worker logs
docker compose logs -f queue-worker

# Process single job
docker compose exec app php artisan queue:work --once
```

### Database Access
```bash
# psql client
docker compose exec postgres psql -U postgres -d moneycast

# Laravel tinker
docker compose exec app php artisan tinker
```

## Performance

### Xdebug Overhead
Xdebug adds ~20-30% overhead. Disable when not debugging:
```bash
# Disable Xdebug
export XDEBUG_MODE=off
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

### Hot Reload Performance
Vite HMR is fast (<100ms updates). If slow:
- Check Docker disk I/O
- Reduce watched files in `vite.config.ts`
- Use native file system on macOS/Windows

## Troubleshooting

### Vite Not Connecting
```bash
# Check Vite logs
docker compose logs vite

# Restart Vite
docker compose restart vite
```

### Xdebug Not Working
```bash
# Verify Xdebug loaded
docker compose exec app php -v | grep Xdebug

# Check Xdebug log
docker compose exec app cat /tmp/xdebug.log
```

### Mailpit Not Receiving Emails
```bash
# Check Laravel mail config
docker compose exec app php artisan config:show mail

# Verify Mailpit running
curl http://localhost:8025
```

## Production vs Development

| Feature | Production | Development |
|---------|-----------|-------------|
| Source Code | Copied into image | Volume mounted |
| Xdebug | ❌ Disabled | ✅ Enabled |
| Vite | ❌ Built assets | ✅ HMR dev server |
| Mailpit | ❌ None | ✅ Email testing |
| Debug Mode | ❌ false | ✅ true |
| Asset Cache | ✅ 1 year | ❌ No cache |
