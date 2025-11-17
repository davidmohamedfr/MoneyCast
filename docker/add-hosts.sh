#!/bin/bash

echo "Adding local development domains to /etc/hosts..."
echo ""
echo "You will be prompted for your password to edit /etc/hosts"
echo ""

# Check if entries already exist
if grep -q "moneycast.local" /etc/hosts; then
    echo "✅ Domains already exist in /etc/hosts"
else
    echo "127.0.0.1 moneycast.local mailpit.moneycast.local vite.moneycast.local" | sudo tee -a /etc/hosts
    echo "✅ Domains added to /etc/hosts"
fi

echo ""
echo "Development URLs:"
echo "  - App:     http://moneycast.local"
echo "  - Mailpit: http://mailpit.moneycast.local"
echo "  - Vite:    http://vite.moneycast.local"
