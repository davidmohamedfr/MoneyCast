# Local Development Domains

## Setup

Add these entries to your `/etc/hosts` file:

```bash
sudo nano /etc/hosts
```

Add these lines:
```
127.0.0.1 moneycast.local
127.0.0.1 mailpit.moneycast.local
127.0.0.1 vite.moneycast.local
```

Or run this command:
```bash
echo "127.0.0.1 moneycast.local mailpit.moneycast.local vite.moneycast.local" | sudo tee -a /etc/hosts
```

## Access URLs

After setup, access your services at:

- **Laravel App**: http://moneycast.local
- **Mailpit**: http://mailpit.moneycast.local
- **Vite HMR**: http://vite.moneycast.local

## Nginx Configuration

The nginx config needs to be updated to handle these domains.
