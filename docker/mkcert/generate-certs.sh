#!/bin/sh

set -e

# Wait for Docker to be ready (in case of timing issues)
sleep 2

# Install local CA if not already installed
if ! mkcert -CAROOT >/dev/null 2>&1; then
    echo "Installing local CA..."
    mkcert -install
fi

# Generate certificates for moneycast.local and *.moneycast.local
echo "Generating SSL certificates for moneycast.local..."
mkcert -cert-file /certs/moneycast.local.pem -key-file /certs/moneycast.local-key.pem moneycast.local *.moneycast.local

echo "SSL certificates generated successfully:"
echo "  Certificate: /certs/moneycast.local.pem"
echo "  Private Key: /certs/moneycast.local-key.pem"

# Keep container running to allow certificate inspection if needed
echo "Certificate generation complete. Container will exit."
ls -la /certs/