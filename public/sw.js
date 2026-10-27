// Nombre del caché actual - v5 actualización obligatoria de layout, logos e iconos
const CACHE_NAME = 'concejo-chia-v5-prod';

// Recursos estáticos para precargar en la instalación
const STATIC_ASSETS = [
  '/',
  '/noticias/',
  '/agenda/',
  '/recorrido/',
  '/transmisiones/',
  '/mas/'
];

// Evento de instalación: guardar recursos estáticos en el caché
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[Service Worker] Precargando recursos estáticos v4');
      return cache.addAll(STATIC_ASSETS);
    })
  );
});

// Evento de activación: purgar TODOS los cachés antiguos de inmediato
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[Service Worker] Eliminando caché antiguo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Evento de recuperación (fetch): Network First para HTML, CSS, JS y API
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // Estrategia Network First para HTML, API, CSS y JS para evitar cacheamiento persistente de layouts viejos
  if (
    url.pathname.startsWith('/api/') || 
    event.request.mode === 'navigate' ||
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.js')
  ) {
    event.respondWith(
      fetch(event.request)
        .then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return networkResponse;
        })
        .catch(() => {
          return caches.match(event.request).then(cached => {
            return cached || caches.match('/');
          });
        })
    );
  } else {
    // Cache First solo para imágenes e íconos estáticos
    event.respondWith(
      caches.match(event.request).then((cachedResponse) => {
        if (cachedResponse) return cachedResponse;

        return fetch(event.request).then((networkResponse) => {
          if (!networkResponse || networkResponse.status !== 200) {
            return networkResponse;
          }
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });
          return networkResponse;
        });
      })
    );
  }
});
