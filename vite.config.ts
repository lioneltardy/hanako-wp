import { resolve } from 'node:path';
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import copy from 'rollup-plugin-copy';

export default defineConfig(({ mode }) => ({
  plugins: [tailwindcss()],
  css: {
    transformer: 'lightningcss',
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    sourcemap: mode !== 'production',
    cssMinify: 'lightningcss',
    rollupOptions: {
      plugins: [
        copy({
          targets: [{ src: 'views/assets/**/*', dest: 'dist/assets' }],
          hook: 'writeBundle',
        }),
      ],
      input: {
        script: resolve(__dirname, 'views/ts/script.ts'),
        style: resolve(__dirname, 'views/css/style.css'),
        'editor-style': resolve(__dirname, 'views/css/editor-style.css'),
      },
      output: {
        entryFileNames: 'js/[name]-[hash].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: ({ name }) => {
          if (name?.endsWith('.css')) {
            return 'css/[name]-[hash][extname]';
          }
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },
}));
