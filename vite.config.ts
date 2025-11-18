import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const appUrl = env.APP_URL || 'http://localhost';
    const viteDevServer = env.VITE_DEV_SERVER_URL || 'http://localhost:5173';

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
                command:
                    'echo "Skipping Wayfinder type generation - using pre-generated types from app container"',
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
            allowedHosts: true,
            https: {
                cert: '/certs/moneycast.local.pem',
                key: '/certs/moneycast.local-key.pem',
            },
            cors: {
                origin: [appUrl, viteDevServer, 'http://localhost', 'https://localhost', 'https://moneycast.local', 'http://moneycast.local'],
                credentials: true,
            },
        },
    };
});
