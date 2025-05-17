import type { MetadataRoute } from 'next'
 
export default function manifest(): MetadataRoute.Manifest {
  return {
    name: 'Journa News',
    short_name: 'JN',
    description: 'Journa News - Your trusted source for latest news',
    start_url: '/',
    display: 'standalone',
    background_color: '#ffffff',
    theme_color: '#f59e0b', // Tailwind amber-500
    categories: ['news', 'media'],
    orientation: 'portrait',
    dir: 'rtl',
    lang: 'fa',
    icons: [
      {
        src: '/favicon.png',
        sizes: '192x192',
        type: 'image/png',
        purpose: 'any',
      },
      {
        src: '/favicon.png',
        sizes: '512x512',
        type: 'image/png',
        purpose: 'any',
      },
    ],
    shortcuts: [
      {
        name: 'Latest News',
        url: '/latest',
        icons: [{ src: '/favicon.png', sizes: '96x96' }],
      },
    ],
  }
}