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
                command: false, // Skip type generation - using pre-generated types from app container
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
            origin: viteDevServer,
            allowedHosts: [appHostname, viteHostname, 'localhost', '127.0.0.1'],
            ...(viteHttps && {
                https: {
                    cert: '/certs/moneycast.local.pem',
                    key: '/certs/moneycast.local-key.pem',
                },
            }),
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
