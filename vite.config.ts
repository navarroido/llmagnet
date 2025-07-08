import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';
import { copyFileSync, mkdirSync } from 'fs';

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    react(),
    {
      name: 'copy-image-files',
      closeBundle() {
        // Create assets directory if it doesn't exist
        const assetsDir = path.resolve(__dirname, 'assets/react-build/assets');
        try {
          mkdirSync(assetsDir, { recursive: true });
        } catch (err) {
          console.error('Error creating assets directory:', err);
        }

        // Copy SVG files
        try {
          copyFileSync(
            path.resolve(__dirname, 'src/assets/images/fkjogo.svg'),
            path.resolve(__dirname, 'assets/react-build/assets/fkjogo.svg')
          );
          console.log('SVG file copied successfully');
        } catch (err) {
          console.error('Error copying SVG file:', err);
        }
        
        // Copy banner image
        try {
          copyFileSync(
            path.resolve(__dirname, 'src/assets/images/banner_upgrade.png'),
            path.resolve(__dirname, 'assets/react-build/assets/banner_upgrade.png')
          );
          console.log('Banner image copied successfully');
        } catch (err) {
          console.error('Error copying banner image:', err);
        }
      }
    }
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  build: {
    outDir: 'assets/react-build',
    emptyOutDir: true,
    sourcemap: true,
    rollupOptions: {
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: ({name}) => {
          if (/\.(css)$/.test(name ?? '')) {
            return 'css/[name][extname]';
          }
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },
}); 