# MoneyCast - Warden Development Environment

This document describes the Warden-based Docker development environment for MoneyCast.

## Overview

MoneyCast uses [Warden](https://docs.warden.dev/) to provide a streamlined, optimized Docker development environment with:

- **PHP 8.4** with FPM and all required extensions
- **PostgreSQL** (Supabase edition with extensions)
- **Redis** for caching, sessions, and queues
- **Nginx** with automatic HTTPS via Traefik
- **Meilisearch** for full-text search
- **Netdata** for system monitoring
- **Queue Workers** for background job processing
- **Vite** with Hot Module Replacement (HMR)
- **Mailhog** for email testing
- **Automatic SSL certificates** via mkcert
- **Local domain routing** via Dnsmasq (no /etc/hosts editing)

## Quick Start

### Prerequisites

1. **Docker Desktop** (macOS/Windows) or Docker Engine (Linux)
2. **Warden 0.15.0+** - [Installation Guide](https://docs.warden.dev/installing.html)

### One-Command Setup

```bash
./bin/setup.sh
```

This script will:
- ✅ Check Warden and Docker are installed
- ✅ Start Warden global services (Traefik, Dnsmasq, etc.)
- ✅ Create `.env` from template
- ✅ Start all Docker containers
- ✅ Install Composer and npm dependencies
- ✅ Run database migrations
- ✅ Generate Wayfinder TypeScript types
- ✅ Build frontend assets

### Manual Setup

If you prefer step-by-step control:

```bash
# 1. Start Warden global services
warden svc up

# 2. Copy environment file
cp .env.warden.dev.example .env.warden.dev
ln -sf .env.warden.dev .env

# 3. Start environment
warden env up -d

# 4. Install dependencies
warden env exec php-fpm composer install
warden env exec php-fpm npm install

# 5. Generate app key
warden env exec php-fpm php artisan key:generate

# 6. Run migrations
warden env exec php-fpm php artisan migrate

# 7. Generate Wayfinder types
warden env exec php-fpm php artisan wayfinder:generate --with-form

# 8. Build assets
warden env exec php-fpm npm run build
```

## Available URLs

Once the environment is running, access these URLs in your browser:

| Service | URL | Description |
|---------|-----|-------------|
| **Main App** | https://moneycast.local | Laravel application |
| **Supabase Studio** | https://supabase.moneycast.local | Database management UI |
| **Meilisearch** | https://meilisearch.moneycast.local | Search engine dashboard |
| **Netdata** | https://netdata.moneycast.local | System monitoring |
| **Mailhog** | https://mailhog.moneycast.test | Email testing inbox |
| **Forecast API** | https://forecast.moneycast.local | Forecasting service (coming soon) |

## Daily Commands

### Starting & Stopping

```bash
# Start environment
warden env up

# Start in background
warden env up -d

# Stop environment
warden env down

# Stop and remove volumes (DANGER: deletes data!)
warden env down -v

# Restart environment
warden env restart
```

### Accessing Containers

```bash
# Access PHP shell (main working container)
warden shell

# Access database (PostgreSQL CLI)
warden db

# Access specific container shell
warden env exec <service> sh

# Examples:
warden env exec redis sh
warden env exec nginx sh
```

### Running Commands

```bash
# Artisan commands
warden env exec php-fpm php artisan <command>

# Examples:
warden env exec php-fpm php artisan migrate
warden env exec php-fpm php artisan queue:work
warden env exec php-fpm php artisan tinker

# Composer
warden env exec php-fpm composer install
warden env exec php-fpm composer require vendor/package

# npm/Node
warden env exec php-fpm npm install
warden env exec php-fpm npm run dev
warden env exec php-fpm npm run build

# Run tests
warden env exec php-fpm php artisan test
warden env exec php-fpm vendor/bin/pest
```

### Frontend Development

```bash
# Start Vite dev server with HMR
warden env exec php-fpm npm run dev

# Build for production
warden env exec php-fpm npm run build

# Run linters
warden env exec php-fpm npm run lint
warden env exec php-fpm npm run format
```

### Queue Workers

Queue workers are automatically started as separate containers. View their status:

```bash
# View all containers
warden env ps

# View queue worker logs
warden env logs queue-worker -f
warden env logs queue-worker-high -f

# Restart queue workers
warden env restart queue-worker queue-worker-high
```

### Database Operations

```bash
# Access PostgreSQL CLI
warden db

# Run migrations
warden env exec php-fpm php artisan migrate

# Rollback migrations
warden env exec php-fpm php artisan migrate:rollback

# Fresh database with seeders
warden env exec php-fpm php artisan migrate:fresh --seed

# Create database backup
docker exec moneycast_db pg_dump -U postgres moneycast > backup.sql

# Restore database backup
cat backup.sql | warden db
```

### Debugging

#### Xdebug

```bash
# Enable Xdebug
warden debug on

# Disable Xdebug
warden debug off

# Check Xdebug status
warden env exec php-fpm php -v | grep Xdebug
```

Configure your IDE to listen on port 9003 for Xdebug connections.

#### Logs

```bash
# View all logs
warden env logs -f

# View specific service logs
warden env logs php-fpm -f
warden env logs nginx -f
warden env logs postgres -f
warden env logs redis -f

# Laravel logs (inside container)
warden env exec php-fpm tail -f storage/logs/laravel.log
```

## Environment Management

### Switching Environments

Three environments are available:

1. **Development** (`.env.warden.dev`) - Default, full debugging
2. **Staging** (`.env.warden.staging`) - Staging testing
3. **Production** (`.env.warden.prod`) - Production-like (Warden not recommended for actual production)

```bash
# Switch to staging
rm .env
ln -sf .env.warden.staging .env
warden env restart

# Switch back to development
rm .env
ln -sf .env.warden.dev .env
warden env restart
```

### Environment Variables

Key variables in `.env`:

```bash
# Warden Configuration
WARDEN_ENV_NAME=moneycast
WARDEN_ENV_TYPE=laravel
TRAEFIK_DOMAIN=moneycast.local

# Database
DB_HOST=db                   # Warden PostgreSQL service
DB_DATABASE=moneycast
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Redis
REDIS_HOST=redis             # Warden Redis service
REDIS_PASSWORD=devpassword

# Mail
MAIL_HOST=mailhog            # Warden Mailhog service
MAIL_PORT=1025

# Meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=masterKey123
```

## Services Architecture

### Core Services (Warden Built-in)

- **php-fpm**: PHP 8.4-FPM Alpine with extensions
- **nginx**: Nginx 1.25 with custom configs
- **db**: PostgreSQL 15 (Supabase edition)
- **redis**: Redis 7 Alpine with AOF persistence
- **mailhog**: Email testing server

### Custom Services (`.warden/warden-env.yml`)

- **meilisearch**: Full-text search engine
- **netdata**: System monitoring
- **queue-worker**: Default priority queue processor
- **queue-worker-high**: High priority queue processor
- **forecast**: Forecasting API placeholder

### Supabase Stack (Optional)

Located in `.warden/docker-compose.supabase.yml`:

- **supabase-studio**: Database management UI
- **supabase-auth**: GoTrue authentication service
- **supabase-rest**: PostgREST API
- **supabase-realtime**: Phoenix realtime server
- **supabase-storage**: S3-compatible object storage
- **supabase-kong**: API gateway
- **supabase-meta**: Database metadata API
- **supabase-analytics**: Logflare logging

To enable Supabase:

```bash
warden env up -d -f .warden/docker-compose.supabase.yml
```

## Performance Optimization

### Mutagen Sync (macOS)

Warden automatically uses Mutagen on macOS for faster file system performance:

```bash
# Check Mutagen status
warden sync list

# Pause sync
warden sync pause

# Resume sync
warden sync resume

# Reset sync
warden sync reset
```

### Build Caching

Warden uses BuildKit for faster builds:

```bash
# Rebuild with cache
warden env build

# Force rebuild without cache
warden env build --no-cache
```

## Troubleshooting

### Containers Won't Start

```bash
# Check Warden global services
warden svc ps

# Restart global services
warden svc down
warden svc up

# Check for port conflicts
lsof -i :80
lsof -i :443
```

### SSL Certificate Issues

```bash
# Recreate SSL certificates
warden sign-certificate moneycast.local
warden sign-certificate supabase.moneycast.local
warden sign-certificate meilisearch.moneycast.local
warden sign-certificate netdata.moneycast.local

# Reinstall CA certificate (macOS)
mkcert -install
```

### DNS Not Resolving

```bash
# Check Dnsmasq is running
warden svc ps dnsmasq

# Restart Dnsmasq
warden svc restart dnsmasq

# Test DNS resolution
dig moneycast.local @127.0.0.1
nslookup moneycast.local 127.0.0.1
```

### Database Connection Errors

```bash
# Check database is running
warden env ps db

# Check database logs
warden env logs db

# Verify database exists
warden db -c "\\l"

# Create database if missing
warden env exec db createdb -U postgres moneycast
```

### Queue Workers Not Processing Jobs

```bash
# Check worker status
warden env ps queue-worker queue-worker-high

# View worker logs
warden env logs queue-worker -f

# Manually run queue worker for debugging
warden env exec php-fpm php artisan queue:work --queue=default --verbose

# Check Redis connection
warden env exec redis redis-cli -a devpassword ping
```

### Vite HMR Not Working

```bash
# Check Vite is running
warden env exec php-fpm ps aux | grep vite

# View Vite logs
warden env logs php-fpm | grep vite

# Restart with polling (for file watching issues)
warden env exec php-fpm npm run dev

# Check WebSocket connection in browser console
# Should see: [vite] connected.
```

### Permission Issues

```bash
# Fix storage permissions
warden env exec php-fpm chmod -R 775 storage bootstrap/cache
warden env exec php-fpm chown -R www-data:www-data storage bootstrap/cache
```

## Migration from Docker Compose

If you're migrating from the legacy Docker Compose setup:

### Key Differences

| Feature | Old Docker Compose | Warden |
|---------|-------------------|--------|
| Service Names | `moneycast_app` | `moneycast_php-fpm` |
| Database Host | `postgres` | `db` |
| Commands | `docker compose exec app` | `warden env exec php-fpm` |
| Domains | Manual /etc/hosts | Automatic via Dnsmasq |
| SSL | Manual mkcert | Automatic via Traefik |

### Migration Steps

1. **Stop old containers**:
   ```bash
   docker compose -f docker/legacy/docker-compose.yml down
   ```

2. **Update `.env`**:
   ```bash
   # Change database host
   DB_HOST=db  # was: postgres

   # Change Redis host
   REDIS_HOST=redis  # was: redis (same)

   # Change mail host
   MAIL_HOST=mailhog  # was: mailpit
   ```

3. **Start Warden**:
   ```bash
   ./bin/setup.sh
   ```

### Rollback to Docker Compose

If needed, legacy Docker Compose files are preserved:

```bash
cd docker/legacy
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d
```

## Performance Benchmarks

Target startup performance:

- **Cold start** (first time): <5s
- **Hot start** (cached): <3s
- **Service health checks**: <10s total

Measure startup time:

```bash
time warden env up -d
```

## Additional Resources

- [Warden Official Documentation](https://docs.warden.dev/)
- [Warden Laravel Environment Guide](https://docs.warden.dev/environments/laravel.html)
- [Warden GitHub Repository](https://github.com/wardenenv/warden)
- [Traefik Documentation](https://doc.traefik.io/traefik/)
- [Supabase Self-Hosting Guide](https://supabase.com/docs/guides/self-hosting)

## Support

For issues or questions:

1. Check this documentation
2. Review [Warden Issues](https://github.com/wardenenv/warden/issues)
3. Check project issues in GitHub
4. Ask the team in Discord/Slack

---

**Last Updated**: 2025-11-22
**Warden Version**: 0.15.0
**Environment Type**: Laravel
