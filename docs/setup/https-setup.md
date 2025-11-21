# HTTPS Local Development Setup

This document describes how to set up HTTPS for local development using browser-trusted SSL certificates with mkcert.

## Overview

The HTTPS setup provides:
- Browser-trusted SSL certificates for `moneycast.local` and `*.moneycast.local`
- Full Vite HMR support over HTTPS
- Automatic HTTP to HTTPS redirects
- Secure cookie handling

## Prerequisites

- Docker and Docker Compose installed
- macOS, Linux, or Windows (WSL2) host system
- `sudo` access for system certificate trust (one-time setup)

## Quick Start

1. **Clone the repository** (if not already done)
   ```bash
   git clone <repository-url>
   cd MoneyCast
   ```

2. **Run the automated setup script**
   ```bash
   ./scripts/setup-https.sh
   ```

3. **Start the development environment**
   ```bash
   composer dev
   ```

4. **Access the application**
   - Open `https://moneycast.local` in your browser
   - Accept any SSL certificate warnings (should be minimal after setup)

## Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Browser       │────│   Nginx (443)   │────│   Laravel App   │
│                 │    │                 │    │                 │
│ moneycast.local │    │ SSL Termination │    │   PHP-FPM       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Vite HMR      │────│   Vite (5173)   │    │   Database      │
│   WebSocket     │    │   Dev Server    │    │   Redis         │
│   (WSS)         │    │   HTTPS          │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Services

- **cert-generator**: Init container that generates SSL certificates using mkcert
- **vite**: Vite development server with HTTPS support
- **nginx**: Reverse proxy with SSL termination
- **app**: Laravel application (PHP-FPM)
- **postgres**: PostgreSQL database
- **redis**: Redis cache and queues

## Manual Setup (Alternative)

If the automated script doesn't work, follow these steps:

### 1. Install mkcert

**macOS:**
```bash
brew install mkcert
brew install nss # for Firefox support
```

**Ubuntu/Debian:**
```bash
sudo apt install libnss3-tools
wget -O mkcert https://github.com/FiloSottile/mkcert/releases/download/v1.4.4/mkcert-v1.4.4-linux-amd64
chmod +x mkcert
sudo mv mkcert /usr/local/bin/
```

**Other Linux:**
```bash
# Download from https://github.com/FiloSottile/mkcert/releases
# Make executable and move to PATH
```

### 2. Install Local CA

```bash
mkcert -install
```

**macOS:** Adds to System Keychain (requires password)
**Linux:** Adds to system certificate store

### 3. Update /etc/hosts

Add this line to `/etc/hosts`:
```
127.0.0.1 moneycast.local
```

### 4. Start Services

```bash
# Start all services including HTTPS components
docker-compose --profile https up -d

# Or with newer Docker Compose
docker compose --profile https up -d
```

### 5. Generate Certificates

The `cert-generator` service will automatically generate certificates on first run.

### 6. Start Development

```bash
composer dev
```

## Troubleshooting

### Certificate Warnings Still Appear

1. **Check CA installation:**
   ```bash
   mkcert -install
   ```

2. **Restart browser** after CA installation

3. **Check certificate validity:**
   ```bash
   openssl x509 -in ssl_certs/_data/moneycast.local.pem -text -noout | grep -A 2 "Validity"
   ```

4. **Regenerate certificates:**
   ```bash
   docker-compose --profile https down -v
   docker-compose --profile https up -d
   ```

### HMR Not Working

1. **Check Vite server logs:**
   ```bash
   docker-compose logs vite
   ```

2. **Verify WebSocket connection** in browser DevTools (Network tab should show `wss://` connections)

3. **Check firewall** blocking port 5173

### Connection Refused

1. **Verify services are running:**
   ```bash
   docker-compose ps
   ```

2. **Check service health:**
   ```bash
   docker-compose exec nginx wget --quiet --tries=1 --spider http://localhost/
   ```

3. **Restart services:**
   ```bash
   docker-compose --profile https restart
   ```

### Certificate Mismatch

- Ensure you're accessing `https://moneycast.local` (not localhost)
- Check `/etc/hosts` contains the correct entry
- Regenerate certificates if domain changed

## Certificate Management

### Regeneration

If certificates expire or need to be recreated:

```bash
# Remove old certificates
docker-compose --profile https down -v

# Restart to generate new certificates
docker-compose --profile https up -d
```

### Certificate Validity

- mkcert certificates are valid for **10 years**
- Issued for: `moneycast.local` and `*.moneycast.local`

### Viewing Certificates

```bash
# View certificate details
openssl x509 -in ssl_certs/_data/moneycast.local.pem -text -noout

# Check certificate chain
openssl verify -CAfile ssl_certs/_data/moneycast.local.pem ssl_certs/_data/moneycast.local.pem
```

## Development Workflow

### Starting Development

```bash
# Start Docker stack (one-time or when needed)
docker-compose --profile https up -d

# Start Laravel development server
composer dev
```

### Daily Development

After initial setup, use:

```bash
composer dev
```

The Docker stack should remain running in the background.

### Stopping Services

```bash
# Stop all services
docker-compose --profile https down

# Stop and remove volumes (resets certificates)
docker-compose --profile https down -v
```

## Team Onboarding

New team members should:

1. Run `./scripts/setup-https.sh` once
2. Use `composer dev` for daily development
3. Access `https://moneycast.local` in browser

## Security Notes

- Certificates are **only valid locally** (not trusted by real CAs)
- HTTPS is **terminated at Nginx** (internal traffic is HTTP)
- Session cookies are **secure** (HTTPS-only)
- HSTS header is set with **short max-age** for development flexibility

## Performance Impact

- Minimal performance impact in development
- SSL termination adds ~1-2ms latency
- Vite HMR works over secure WebSocket connections

## FAQ

### Q: Do I need to run this every time?

**A:** No, run `./scripts/setup-https.sh` once. Then use `composer dev` daily.

### Q: Can I use different domains?

**A:** Yes, update the certificate generation script and Docker configs.

### Q: What if I get "Permission denied" errors?

**A:** Ensure Docker is running and you have proper permissions.

### Q: Does this work on Windows?

**A:** Yes, via WSL2. Run the setup script from WSL2 environment.

### Q: How do I disable HTTPS temporarily?

**A:** Use regular `docker-compose up -d` without the `--profile https` flag.

### Q: Are certificates shared between team members?

**A:** No, each developer generates their own certificates locally.

### Q: What happens if certificates expire?

**A:** Regenerate them using the certificate regeneration steps above.

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review Docker logs: `docker-compose logs`
3. Verify system requirements
4. Check GitHub issues for similar problems

## Related Files

- `docker-compose.yml` - Service definitions
- `docker/mkcert/` - Certificate generation
- `docker/nginx/conf.d/app-ssl.dev.conf` - HTTPS app configuration
- `docker/nginx/conf.d/vite-ssl.dev.conf` - HTTPS Vite configuration
- `vite.config.ts` - Vite HTTPS configuration
- `scripts/setup-https.sh` - Automated setup script
