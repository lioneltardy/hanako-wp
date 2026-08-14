import { resolve } from 'node:path';
import { defineConfig, createLogger } from 'vite';
import copy from 'rollup-plugin-copy';

const FONT_EXTENSIONS = /\.(woff2?|ttf|otf|eot)$/i;

export default defineConfig(({ mode }) => ({
  base: './',
  plugins: [],
  css: {
    transformer: 'postcss',
  },
  customLogger: {
    ...createLogger(),
    warn(msg, options) {
      if (msg.includes('resolved at runtime')) return;
      createLogger().warn(msg, options);
    },
    warnOnce(msg, options) {
      if (msg.includes('resolved at runtime')) return;
      createLogger().warnOnce?.(msg, options);
    },
  },
  build: {
    target: ['safari12', 'chrome90', 'firefox88'],
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    sourcemap: mode !== 'production',
    cssMinify: true,
    rollupOptions: {
      plugins: [
        copy({
          targets: [
            { src: 'views/assets/icons/**/*', dest: 'dist/assets/icons' },
            { src: 'views/assets/images/**/*', dest: 'dist/assets/images' },
          ],
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
          if (name && FONT_EXTENSIONS.test(name)) {
            return 'assets/fonts/[name][extname]';
          }
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },
}));
