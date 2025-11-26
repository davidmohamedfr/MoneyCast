#!/usr/bin/env bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_header() {
    echo ""
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo ""
}

# Check if Warden is installed
check_warden() {
    print_header "Checking Warden Installation"

    if ! command -v warden &> /dev/null; then
        print_error "Warden is not installed"
        print_info "Install Warden: https://docs.warden.dev/installing.html"
        exit 1
    fi

    WARDEN_VERSION=$(warden version 2>&1 | head -n1 || echo "unknown")
    print_success "Warden is installed: $WARDEN_VERSION"
}

# Check Docker is running
check_docker() {
    print_header "Checking Docker"

    if ! docker info &> /dev/null; then
        print_error "Docker is not running"
        print_info "Please start Docker Desktop and try again"
        exit 1
    fi

    print_success "Docker is running"
}

# Start Warden global services
start_warden_services() {
    print_header "Starting Warden Global Services"

    print_info "Starting Traefik, Dnsmasq, Mailhog, and Tunnel..."
    warden svc up

    print_success "Warden global services started"
}

# Configure environment file
configure_environment() {
    print_header "Configuring Environment"

    if [ ! -f .env ]; then
        print_info "No .env file found, creating from .env.warden.dev.example"
        cp .env.warden.dev.example .env.warden.dev
        ln -sf .env.warden.dev .env
        print_success ".env file created and symlinked to .env.warden.dev"
    else
        print_success ".env file already exists"
    fi

    # Generate APP_KEY if not set
    if ! grep -q "APP_KEY=base64:" .env; then
        print_info "Generating Laravel application key..."
        warden env exec php-fpm php artisan key:generate
        print_success "Application key generated"
    else
        print_success "Application key already exists"
    fi
}

# Start environment
start_environment() {
    print_header "Starting Warden Environment"

    print_info "Starting all Docker containers..."
    warden env up -d

    print_success "Environment started"
}

# Install dependencies
install_dependencies() {
    print_header "Installing Dependencies"

    print_info "Installing Composer dependencies..."
    warden env exec php-fpm composer install
    print_success "Composer dependencies installed"

    print_info "Installing npm dependencies..."
    warden env exec php-fpm npm install
    print_success "npm dependencies installed"
}

# Run migrations
run_migrations() {
    print_header "Running Database Migrations"

    print_info "Waiting for database to be ready..."
    sleep 5

    print_info "Running migrations..."
    warden env exec php-fpm php artisan migrate --force
    print_success "Migrations completed"
}

# Generate Wayfinder types
generate_wayfinder_types() {
    print_header "Generating Wayfinder Types"

    print_info "Generating TypeScript route types..."
    warden env exec php-fpm php artisan wayfinder:generate --with-form
    print_success "Wayfinder types generated"
}

# Build assets
build_assets() {
    print_header "Building Frontend Assets"

    print_info "Building Vite assets..."
    warden env exec php-fpm npm run build
    print_success "Assets built"
}

# Display success message
display_success() {
    print_header "Setup Complete! 🎉"

    echo ""
    echo -e "${GREEN}Your MoneyCast development environment is ready!${NC}"
    echo ""
    echo -e "${BLUE}Available URLs:${NC}"
    echo -e "  • Main Application:  ${GREEN}https://moneycast.local${NC}"
    echo -e "  • Supabase Studio:   ${GREEN}https://supabase.moneycast.local${NC}"
    echo -e "  • Meilisearch:       ${GREEN}https://meilisearch.moneycast.local${NC}"
    echo -e "  • Netdata:           ${GREEN}https://netdata.moneycast.local${NC}"
    echo -e "  • Mailhog:           ${GREEN}https://mailhog.moneycast.test${NC}"
    echo ""
    echo -e "${BLUE}Useful Commands:${NC}"
    echo -e "  • Start environment:   ${YELLOW}warden env up${NC}"
    echo -e "  • Stop environment:    ${YELLOW}warden env down${NC}"
    echo -e "  • Access PHP shell:    ${YELLOW}warden shell${NC}"
    echo -e "  • Access database:     ${YELLOW}warden db${NC}"
    echo -e "  • Run Artisan:         ${YELLOW}warden env exec php-fpm php artisan <command>${NC}"
    echo -e "  • Run npm:             ${YELLOW}warden env exec php-fpm npm <command>${NC}"
    echo -e "  • Start Vite dev:      ${YELLOW}warden env exec php-fpm npm run dev${NC}"
    echo -e "  • View logs:           ${YELLOW}warden env logs -f${NC}"
    echo ""
    echo -e "${BLUE}Documentation:${NC}"
    echo -e "  • Warden Setup: ${YELLOW}./WARDEN_SETUP.md${NC}"
    echo -e "  • Warden Docs:  ${YELLOW}https://docs.warden.dev/${NC}"
    echo ""
}

# Main execution
main() {
    clear
    echo -e "${BLUE}"
    echo "╔══════════════════════════════════════════╗"
    echo "║   MoneyCast Development Setup Script    ║"
    echo "║   Powered by Warden                      ║"
    echo "╚══════════════════════════════════════════╝"
    echo -e "${NC}"

    check_warden
    check_docker
    start_warden_services
    configure_environment
    start_environment
    install_dependencies
    run_migrations
    generate_wayfinder_types
    build_assets
    display_success
}

# Run main function
main
