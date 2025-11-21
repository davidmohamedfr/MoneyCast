.PHONY: help up down restart build logs ps shell artisan composer npm test clean clean-all install

# Default target
.DEFAULT_GOAL := help

## help: Show this help message
help:
	@echo "Usage: make [target]"
	@echo ""
	@echo "Available targets:"
	@grep -E '^## ' Makefile | sed 's/^## /  /'

## up: Start all services in detached mode
up:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d

## down: Stop all services
down:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml down

## restart: Restart all services
restart: down up

## build: Build or rebuild services
build:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml build

## rebuild: Rebuild services without cache
rebuild:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml build --no-cache

## logs: View logs from all services (use 'make logs-app' for specific service)
logs:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f

## logs-app: View Laravel app logs
logs-app:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f app

## logs-nginx: View nginx logs
logs-nginx:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f nginx

## logs-vite: View Vite dev server logs
logs-vite:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml logs -f vite

## ps: List all running services
ps:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml ps

## shell: Open bash shell in app container
shell:
	docker exec -it moneycast_app sh

## artisan: Run artisan commands (use 'make artisan cmd="migrate"')
artisan:
	@./artisand $(cmd)

## composer: Run composer commands (use 'make composer cmd="install"')
composer:
	docker exec moneycast_app composer $(cmd)

## npm: Run npm commands (use 'make npm cmd="install"')
npm:
	docker exec moneycast_vite npm $(cmd)

## test: Run tests
test:
	docker exec moneycast_app php artisan test

## migrate: Run database migrations
migrate:
	@./artisand migrate

## migrate-fresh: Drop all tables and re-run migrations
migrate-fresh:
	@./artisand migrate:fresh --seed

## seed: Run database seeders
seed:
	@./artisand db:seed

## cache-clear: Clear all caches
cache-clear:
	@./artisand cache:clear
	@./artisand config:clear
	@./artisand route:clear
	@./artisand view:clear

## optimize: Optimize the application
optimize:
	@./artisand config:cache
	@./artisand route:cache
	@./artisand view:cache

## clean: Stop and remove all containers (keeps volumes)
clean:
	docker compose -f docker-compose.yml -f docker-compose.dev.yml down --remove-orphans

## clean-all: Remove all containers, volumes, and orphans (DESTRUCTIVE)
clean-all:
	@echo "⚠️  WARNING: This will DELETE all volumes including databases!"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker compose -f docker-compose.yml -f docker-compose.dev.yml down -v --remove-orphans; \
	else \
		echo "Aborted."; \
	fi

## install: Initial setup - build, start services, install dependencies
install:
	@echo "Generating secure environment file..."
	@if [ ! -f .env ]; then \
		cp .env.dev.example .env; \
		DB_PASS=$$(openssl rand -base64 24 | tr -d '/+=' | head -c 32); \
		REDIS_PASS=$$(openssl rand -base64 24 | tr -d '/+=' | head -c 32); \
		sed -i.bak "s/CHANGE_ME_GENERATE_RANDOM_PASSWORD/$$DB_PASS/1" .env; \
		sed -i.bak "s/CHANGE_ME_GENERATE_RANDOM_PASSWORD/$$REDIS_PASS/2" .env; \
		rm -f .env.bak; \
		echo "✅ Generated random passwords for DB and Redis"; \
	else \
		echo "⚠️  .env already exists, skipping generation"; \
	fi
	@echo "Building services..."
	docker compose -f docker-compose.yml -f docker-compose.dev.yml build || { echo "❌ Failed to build services"; exit 1; }
	@echo "Starting services..."
	docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d || { echo "❌ Failed to start services"; exit 1; }
	@echo "Waiting for services to be ready..."
	@sleep 10
	@echo "Installing composer dependencies..."
	docker exec moneycast_app composer install || { echo "❌ Failed to install composer dependencies"; exit 1; }
	@echo "Generating application key..."
	@./artisand key:generate || { echo "❌ Failed to generate APP_KEY"; exit 1; }
	@echo "Installing npm dependencies and npx in app container..."
	docker exec moneycast_app npm install -g npx concurrently || { echo "❌ Failed to install npx and concurrently in app container"; exit 1; }
	@echo "Setting artisand script executable..."
	@chmod +x artisand
	@echo "Running migrations..."
	@./artisand migrate || { echo "❌ Failed to run migrations"; exit 1; }
	@echo ""
	@echo "✅ Installation complete!"
	@echo ""
	@echo "URLs:"
	@echo "  - App:     http://moneycast.local"
	@echo "  - Mailpit: http://mailpit.moneycast.local"
	@echo "  - Vite:    http://vite.moneycast.local"
	@echo ""
	@echo "Make sure /etc/hosts contains:"
	@echo "  127.0.0.1 moneycast.local mailpit.moneycast.local vite.moneycast.local"

## hosts: Add local domains to /etc/hosts
hosts:
	@./docker/add-hosts.sh || { echo "❌ Failed to update /etc/hosts"; exit 1; }
