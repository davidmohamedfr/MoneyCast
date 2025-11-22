.PHONY: help up down restart build logs ps shell artisan composer npm test clean clean-all install

# Default target
.DEFAULT_GOAL := help

## help: Show this help message
help:
	@echo "MoneyCast - Warden Development Environment"
	@echo ""
	@echo "Usage: make [target]"
	@echo ""
	@echo "Available targets:"
	@grep -E '^## ' Makefile | sed 's/^## /  /'
	@echo ""
	@echo "Warden Commands:"
	@echo "  warden env up        - Start environment"
	@echo "  warden env down      - Stop environment"
	@echo "  warden shell         - Access PHP container"
	@echo "  warden db            - Access database"

## up: Start Warden environment
up:
	warden env up -d

## down: Stop Warden environment
down:
	warden env down

## restart: Restart Warden environment
restart: down up

## build: Build or rebuild services
build:
	warden env build

## rebuild: Rebuild services without cache
rebuild:
	warden env build --no-cache

## logs: View logs from all services
logs:
	warden env logs -f

## logs-app: View PHP-FPM logs
logs-app:
	warden env logs -f php-fpm

## logs-nginx: View nginx logs
logs-nginx:
	warden env logs -f nginx

## logs-queue: View queue worker logs
logs-queue:
	warden env logs -f queue-worker queue-worker-high

## logs-db: View database logs
logs-db:
	warden env logs -f db

## ps: List all running services
ps:
	warden env ps

## status: Show detailed container status
status:
	@docker ps --filter "name=moneycast" --format "table {{.Names}}\t{{.Status}}"

## shell: Open shell in PHP container
shell:
	warden shell

## shell-root: Open shell as root in PHP container
shell-root:
	warden env exec -u root php-fpm sh

## db: Access PostgreSQL database CLI
db:
	warden db

## artisan: Run artisan commands (use 'make artisan cmd="migrate"')
artisan:
	warden env exec php-fpm php artisan $(cmd)

## composer: Run composer commands (use 'make composer cmd="install"')
composer:
	warden env exec php-fpm composer $(cmd)

## npm: Run npm commands (use 'make npm cmd="install"')
npm:
	warden env exec php-fpm npm $(cmd)

## test: Run Pest tests
test:
	warden env exec php-fpm php artisan test

## pest: Run Pest with options (use 'make pest cmd="--filter=ExampleTest"')
pest:
	warden env exec php-fpm vendor/bin/pest $(cmd)

## migrate: Run database migrations
migrate:
	warden env exec php-fpm php artisan migrate

## migrate-fresh: Drop all tables and re-run migrations
migrate-fresh:
	@echo "⚠️  WARNING: This will DROP all database tables!"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		warden env exec php-fpm php artisan migrate:fresh --seed; \
	else \
		echo "Aborted."; \
	fi

## seed: Run database seeders
seed:
	warden env exec php-fpm php artisan db:seed

## tinker: Open Laravel Tinker REPL
tinker:
	warden env exec php-fpm php artisan tinker

## cache-clear: Clear all caches
cache-clear:
	warden env exec php-fpm php artisan cache:clear
	warden env exec php-fpm php artisan config:clear
	warden env exec php-fpm php artisan route:clear
	warden env exec php-fpm php artisan view:clear

## optimize: Optimize the application for production
optimize:
	warden env exec php-fpm php artisan config:cache
	warden env exec php-fpm php artisan route:cache
	warden env exec php-fpm php artisan view:cache
	warden env exec php-fpm php artisan optimize

## wayfinder: Generate Wayfinder TypeScript route types
wayfinder:
	warden env exec php-fpm php artisan wayfinder:generate --with-form

## vite-dev: Start Vite development server
vite-dev:
	warden env exec php-fpm npm run dev

## vite-build: Build production assets
vite-build:
	warden env exec php-fpm npm run build

## queue-restart: Restart queue workers
queue-restart:
	docker restart moneycast-queue-worker-1 moneycast-queue-worker-high-1

## queue-work: Manually run queue worker in foreground (for debugging)
queue-work:
	warden env exec php-fpm php artisan queue:work --verbose

## xdebug-on: Enable Xdebug
xdebug-on:
	warden debug on

## xdebug-off: Disable Xdebug
xdebug-off:
	warden debug off

## clean: Stop and remove all containers (keeps volumes)
clean:
	warden env down --remove-orphans

## clean-all: Remove all containers, volumes, and orphans (DESTRUCTIVE)
clean-all:
	@echo "⚠️  WARNING: This will DELETE all volumes including databases!"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		warden env down -v --remove-orphans; \
		docker volume rm -f supabase_db supabase_storage redis_data meilisearch_data 2>/dev/null || true; \
		echo "✅ All volumes removed"; \
	else \
		echo "Aborted."; \
	fi

## install: Initial setup using automated script
install:
	@./bin/setup.sh

## install-manual: Manual setup steps
install-manual:
	@echo "Starting Warden global services..."
	warden svc up
	@echo "Creating environment file..."
	@if [ ! -f .env ]; then \
		cp .env.warden.dev.example .env.warden.dev; \
		ln -sf .env.warden.dev .env; \
		echo "✅ .env file created"; \
	else \
		echo "⚠️  .env already exists"; \
	fi
	@echo "Starting Warden environment..."
	warden env up -d
	@echo "Waiting for services to be ready..."
	@sleep 15
	@echo "Installing composer dependencies..."
	warden env exec php-fpm composer install
	@echo "Generating application key..."
	warden env exec php-fpm php artisan key:generate
	@echo "Installing npm dependencies..."
	warden env exec php-fpm npm install
	@echo "Running migrations..."
	warden env exec php-fpm php artisan migrate
	@echo "Generating Wayfinder types..."
	warden env exec php-fpm php artisan wayfinder:generate --with-form
	@echo "Building frontend assets..."
	warden env exec php-fpm npm run build
	@echo ""
	@echo "✅ Installation complete!"
	@echo ""
	@echo "Available URLs:"
	@echo "  - App:         https://moneycast.local"
	@echo "  - Meilisearch: https://meilisearch.moneycast.local"
	@echo "  - Netdata:     https://netdata.moneycast.local"
	@echo "  - Mailhog:     https://mailhog.moneycast.test"
	@echo ""
	@echo "No /etc/hosts editing needed - Warden handles DNS automatically!"

## supabase-up: Start Supabase stack
supabase-up:
	@echo "Starting Supabase services..."
	warden env up -d -f .warden/docker-compose.supabase.yml
	@echo "✅ Supabase started at https://supabase.moneycast.local"

## supabase-down: Stop Supabase stack
supabase-down:
	@echo "Stopping Supabase services..."
	docker compose -f .warden/docker-compose.supabase.yml down
	@echo "✅ Supabase stopped"

## env-dev: Switch to development environment
env-dev:
	@rm -f .env
	@ln -sf .env.warden.dev .env
	@echo "✅ Switched to development environment"
	@echo "Run 'make restart' to apply changes"

## env-staging: Switch to staging environment
env-staging:
	@rm -f .env
	@ln -sf .env.warden.staging .env
	@echo "✅ Switched to staging environment"
	@echo "Run 'make restart' to apply changes"

## env-prod: Switch to production environment
env-prod:
	@rm -f .env
	@ln -sf .env.warden.prod .env
	@echo "⚠️  Switched to production environment"
	@echo "Run 'make restart' to apply changes"

## backup-db: Create database backup
backup-db:
	@mkdir -p backups
	@BACKUP_FILE="backups/moneycast_$$(date +%Y%m%d_%H%M%S).sql"; \
	docker exec moneycast-db-1 pg_dump -U postgres moneycast > $$BACKUP_FILE; \
	echo "✅ Database backed up to $$BACKUP_FILE"

## restore-db: Restore database from backup (use 'make restore-db file=backup.sql')
restore-db:
	@if [ -z "$(file)" ]; then \
		echo "❌ Please specify backup file: make restore-db file=backups/moneycast_YYYYMMDD_HHMMSS.sql"; \
		exit 1; \
	fi
	@echo "⚠️  WARNING: This will REPLACE the current database!"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		cat $(file) | warden db; \
		echo "✅ Database restored from $(file)"; \
	else \
		echo "Aborted."; \
	fi

## health: Check health of all services
health:
	@echo "Checking service health..."
	@docker ps --filter "name=moneycast" --format "table {{.Names}}\t{{.Status}}" | grep -E "(healthy|unhealthy|starting)" || echo "All services running"

## urls: Display all available URLs
urls:
	@echo ""
	@echo "🌐 Available URLs:"
	@echo ""
	@echo "  Main Application:"
	@echo "    https://moneycast.local"
	@echo ""
	@echo "  Development Tools:"
	@echo "    https://meilisearch.moneycast.local  - Search Dashboard"
	@echo "    https://netdata.moneycast.local      - System Monitoring"
	@echo "    https://mailhog.moneycast.test       - Email Testing"
	@echo "    https://forecast.moneycast.local     - Forecast API (placeholder)"
	@echo ""
	@echo "  Optional (run 'make supabase-up'):"
	@echo "    https://supabase.moneycast.local     - Supabase Studio"
	@echo ""

## dev: Start development workflow (up + vite)
dev:
	@echo "Starting development environment..."
	@make up
	@echo "Waiting for services..."
	@sleep 10
	@echo "Starting Vite dev server..."
	@make vite-dev

