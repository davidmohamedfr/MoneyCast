#!/bin/bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Function to detect OS
detect_os() {
    if [[ "$OSTYPE" == "darwin"* ]]; then
        echo "macos"
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        if command -v apt-get >/dev/null 2>&1; then
            echo "ubuntu"
        elif command -v dnf >/dev/null 2>&1; then
            echo "fedora"
        elif command -v pacman >/dev/null 2>&1; then
            echo "arch"
        else
            echo "linux"
        fi
    else
        echo "unknown"
    fi
}

# Function to check if running as root/sudo
check_sudo() {
    if [[ $EUID -eq 0 ]]; then
        print_error "This script should not be run as root. Please run as a regular user."
        exit 1
    fi
}

# Function to check Docker and Docker Compose
check_docker() {
    if ! command -v docker >/dev/null 2>&1; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi

    if ! command -v docker-compose >/dev/null 2>&1 && ! docker compose version >/dev/null 2>&1; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi

    print_success "Docker and Docker Compose are installed"
}

# Function to check /etc/hosts entry
check_hosts() {
    if ! grep -q "127.0.0.1 moneycast.local" /etc/hosts; then
        print_warning "/etc/hosts does not contain moneycast.local entry"

        if sudo -n true 2>/dev/null; then
            print_info "Adding moneycast.local to /etc/hosts (requires sudo)..."
            echo "127.0.0.1 moneycast.local" | sudo tee -a /etc/hosts >/dev/null
            print_success "Added moneycast.local to /etc/hosts"
        else
            print_error "Cannot modify /etc/hosts without sudo. Please add the following line manually:"
            echo "127.0.0.1 moneycast.local"
            echo ""
            echo "Then re-run this script."
            exit 1
        fi
    else
        print_success "/etc/hosts already contains moneycast.local"
    fi
}

# Function to start Docker Compose stack
start_docker_stack() {
    print_info "Starting Docker Compose stack..."

    if command -v docker-compose >/dev/null 2>&1; then
        docker-compose --profile https up -d
    else
        docker compose --profile https up -d
    fi

    print_success "Docker Compose stack started"
}

# Function to wait for certificate generation
wait_for_certs() {
    print_info "Waiting for SSL certificate generation..."

    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        # Check if certificates exist in the Docker volume
        if docker run --rm -v moneycast_ssl_certs:/certs alpine test -f /certs/moneycast.local.pem 2>/dev/null; then
            print_success "SSL certificates generated successfully"
            return 0
        fi

        echo -n "."
        sleep 2
        ((attempt++))
    done

    print_error "Certificate generation timed out"
    return 1
}

# Function to extract CA certificate
extract_ca_cert() {
    print_info "Extracting CA certificate from container..."

    local temp_cert=$(mktemp)

    if command -v docker-compose >/dev/null 2>&1; then
        docker-compose exec cert-generator cat /root/.local/share/mkcert/rootCA.pem > "$temp_cert" 2>/dev/null || \
        docker-compose exec cert-generator mkcert -CAROOT | xargs cat | head -n 20 > "$temp_cert" 2>/dev/null || \
        true
    else
        docker compose exec cert-generator cat /root/.local/share/mkcert/rootCA.pem > "$temp_cert" 2>/dev/null || \
        docker compose exec cert-generator mkcert -CAROOT | xargs cat | head -n 20 > "$temp_cert" 2>/dev/null || \
        true
    fi

    if [ -s "$temp_cert" ]; then
        print_success "CA certificate extracted to $temp_cert"
        echo "$temp_cert"
    else
        print_error "Failed to extract CA certificate"
        rm -f "$temp_cert"
        return 1
    fi
}

# Function to trust CA certificate on macOS
trust_ca_macos() {
    local ca_cert=$1

    print_info "Trusting CA certificate on macOS..."

    if sudo -n true 2>/dev/null; then
        sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain "$ca_cert"
        print_success "CA certificate trusted on macOS"
    else
        print_error "Cannot trust CA certificate without sudo. Please run:"
        echo "sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain $ca_cert"
        return 1
    fi
}

# Function to trust CA certificate on Linux
trust_ca_linux() {
    local ca_cert=$1
    local os=$2

    print_info "Trusting CA certificate on Linux ($os)..."

    if sudo -n true 2>/dev/null; then
        case $os in
            ubuntu)
                sudo cp "$ca_cert" /usr/local/share/ca-certificates/moneycast-ca.crt
                sudo update-ca-certificates
                ;;
            fedora)
                sudo cp "$ca_cert" /etc/pki/ca-trust/source/anchors/moneycast-ca.crt
                sudo update-ca-trust
                ;;
            arch)
                sudo cp "$ca_cert" /etc/ca-certificates/trust-source/anchors/moneycast-ca.crt
                sudo trust extract-compat
                ;;
            *)
                print_warning "Unsupported Linux distribution. Please manually trust the CA certificate at: $ca_cert"
                return 1
                ;;
        esac
        print_success "CA certificate trusted on Linux ($os)"
    else
        print_error "Cannot trust CA certificate without sudo. Please manually trust: $ca_cert"
        return 1
    fi
}

# Function to test HTTPS setup
test_https() {
    print_info "Testing HTTPS setup..."

    # Wait a bit for services to be ready
    sleep 5

    if curl -k -s -o /dev/null -w "%{http_code}" https://moneycast.local | grep -q "200\|301\|302"; then
        print_success "HTTPS is working! 🎉"
        print_info "You can now access https://moneycast.local in your browser"
        return 0
    else
        print_warning "HTTPS test failed. Services might still be starting up."
        print_info "Try accessing https://moneycast.local in your browser in a few moments"
        return 1
    fi
}

# Main function
main() {
    echo ""
    print_info "MoneyCast HTTPS Local Development Setup"
    echo "========================================"
    echo ""

    check_sudo
    check_docker
    check_hosts
    start_docker_stack
    wait_for_certs

    local os=$(detect_os)
    local ca_cert=""

    if [ "$os" = "macos" ] || [[ "$os" == *"linux"* ]]; then
        ca_cert=$(extract_ca_cert)
        if [ -n "$ca_cert" ]; then
            case $os in
                macos)
                    trust_ca_macos "$ca_cert"
                    ;;
                *)
                    trust_ca_linux "$ca_cert" "$os"
                    ;;
            esac
        fi
    else
        print_warning "Unsupported OS: $os. Please manually trust the CA certificate."
    fi

    test_https

    echo ""
    print_success "Setup complete!"
    echo ""
    print_info "Next steps:"
    echo "  1. Run 'composer dev' to start the Laravel development server"
    echo "  2. Open https://moneycast.local in your browser"
    echo "  3. Accept any remaining SSL warnings (if any)"
    echo ""
    print_info "For team members, just run this script once to set up HTTPS locally."
    echo ""
}

# Run main function
main "$@"