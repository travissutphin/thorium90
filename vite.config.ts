import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    // Load environment variables
    const env = loadEnv(mode, process.cwd(), '');
    
    // Dynamic port selection with fallback
    const getPort = () => {
        const envPort = parseInt(env.VITE_PORT || '5173');
        return isNaN(envPort) ? 5173 : envPort;
    };

    return {
        server: {
            port: getPort(),
            // Enable HTTPS for OAuth testing and modern features
            https: env.VITE_HTTPS === 'true' ? {
                // Generate self-signed certificate for local development
                // In production, use proper certificates
            } : false,
            host: true, // Allow external connections (useful for mobile testing)
            strictPort: false, // Allow port fallback if configured port is busy
            // Cross-platform compatibility
            watch: {
                usePolling: process.platform === 'win32',
            },
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.tsx'],
                ssr: 'resources/js/ssr.tsx',
                refresh: true,
            }),
            react({
                // Fast refresh configuration
                fastRefresh: true,
                // Include .jsx files for better compatibility
                include: "**/*.{jsx,tsx}",
            }),
            tailwindcss(),
        ],
        esbuild: {
            jsx: 'automatic',
            // Target modern browsers for development
            target: mode === 'development' ? 'esnext' : 'es2020',
        },
        resolve: {
            alias: {
                'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
                '@': resolve(__dirname, 'resources/js'),
                '~': resolve(__dirname, 'resources'),
            },
        },
        // Development optimizations
        optimizeDeps: {
            include: [
                'react',
                'react-dom',
                '@inertiajs/react',
                '@headlessui/react',
                'clsx',
                'tailwind-merge'
            ],
        },
        build: {
            // Source maps for better debugging
            sourcemap: mode === 'development',
            // Rollup options for better tree shaking
            rollupOptions: {
                output: {
                    manualChunks: {
                        vendor: ['react', 'react-dom'],
                        inertia: ['@inertiajs/react'],
                        ui: ['@headlessui/react', '@radix-ui/react-dialog', '@radix-ui/react-dropdown-menu'],
                    },
                },
            },
            // Target modern browsers
            target: 'es2020',
            // Increase chunk size limit for better optimization
            chunkSizeWarningLimit: 1000,
        },
        // Preview server configuration (for production builds)
        preview: {
            port: getPort() + 1000, // Use different port for preview
            host: true,
        },
    };
});
