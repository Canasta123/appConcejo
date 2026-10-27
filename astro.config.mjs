// @ts-check
import { defineConfig } from 'astro/config';

// https://astro.build/config
export default defineConfig({
  site: 'https://app.concejodechia.gov.co',
  build: {
    // Directorios limpios para URLs (/noticias, /agenda, /documentos)
    format: 'directory',
  },
  vite: {
    build: {
      cssMinify: true,
    },
  },
});
