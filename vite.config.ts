import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    // Use process.env for Docker compatibility, fallback to loadEnv for local dev
    const env = loadEnv(mode, process.cwd(), '');
    const appUrl = process.env.APP_URL || env.APP_URL || 'http://localhost';
    const viteDevServer = process.env.VITE_DEV_SERVER_URL || env.VITE_DEV_SERVER_URL || 'http://localhost:5173';
    const viteHttps = process.env.VITE_HTTPS === 'true' || env.VITE_HTTPS === 'true';

    // Extract hostname from URLs for CORS
    const appHostname = new URL(appUrl).hostname;
    const viteHostname = new URL(viteDevServer).hostname;

    return {
        plugins: [
            laravel({
                input: ['resources/js/app.ts'],
                ssr: 'resources/js/ssr.ts',
                refresh: true,
                devServerUrl: viteDevServer,
                hotFile: 'public/hot',
            }),
            tailwindcss(),
            wayfinder({
                formVariants: true,
                command: 'php artisan wayfinder:generate --with-form',
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: true,
            origin: appUrl, // Use main app URL for origin
            allowedHosts: [appHostname, viteHostname, 'localhost', '127.0.0.1'],
            // HMR configuration for Warden/Traefik proxy
            hmr: {
                protocol: 'wss',   // WebSocket Secure through Traefik
                host: appHostname, // Use main app hostname (moneycast.local)
                clientPort: 443,   // Traefik HTTPS port
                path: '/@vite/hmr', // Explicit HMR path
            },
            // Watch configuration for Docker volumes
            watch: {
                usePolling: true,
                interval: 1000,
            },
            cors: {
                origin: [
                    appUrl,
                    viteDevServer,
                    `http://${appHostname}`,
                    `https://${appHostname}`,
                    `http://${viteHostname}`,
                    `https://${viteHostname}`,
                ],
                credentials: true,
            },
        },
    };
});
