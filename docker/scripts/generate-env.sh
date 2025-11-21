#!/bin/bash

set -e

# Function to generate random password
generate_password() {
    openssl rand -base64 32 | tr -d "=+/" | cut -c1-24
}

# Function to generate random database name
generate_db_name() {
    echo "moneycast_dev_$(openssl rand -hex 4)"
}

# Check if .env file exists
if [ -f ".env" ]; then
    echo "⚠️  .env file already exists. This script will only update database credentials."
    echo "   Manual review recommended to ensure no important settings are overwritten."
    echo ""
fi

# Generate credentials
DB_PASSWORD=$(generate_password)
DB_DATABASE=$(generate_db_name)

# Update .env.dev.example with generated credentials
if [ -f ".env.dev.example" ]; then
    sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env.dev.example
    sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env.dev.example
    rm .env.dev.example.bak
    echo "✅ Updated .env.dev.example with random credentials"
fi

# Update .env if it exists and uses the default values
if [ -f ".env" ]; then
    # Only update if still using default postgres/postgres
    if grep -q "DB_PASSWORD=postgres" .env && grep -q "DB_USERNAME=postgres" .env; then
        sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
        sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
        rm .env.bak
        echo "✅ Updated .env with random credentials"
    else
        echo "ℹ️  .env already has custom credentials, skipping update"
    fi
fi

echo ""
echo "🔐 Generated secure development credentials:"
echo "   Database: $DB_DATABASE"
echo "   Password: $DB_PASSWORD"
echo ""
echo "⚠️  IMPORTANT: These credentials are for development only!"
echo "   Never use them in production environments."
echo ""
echo "💡 Tip: Run this script again to generate new random credentials."