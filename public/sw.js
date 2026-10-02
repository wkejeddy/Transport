const CACHE_NAME = 'realvoyage-cache-v2';
const OFFLINE_URLS = [
  '/',
  '/css/app.css',
  '/manifest.json',
  '/images/icons/icon-192.png',
  '/images/icons/icon-512.png',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(OFFLINE_URLS).catch(err => {
        console.warn('Pre-cache non-blocking error:', err);
      });
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(name => name !== CACHE_NAME).map(name => caches.delete(name))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  // Only handle GET requests
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // Skip unsupported schemes (e.g., chrome-extension, data)
  if (!url.protocol.startsWith('http')) return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Cache successful GET responses safely
        if (response && response.status === 200 && response.type === 'basic') {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseClone).catch(() => {});
          }).catch(() => {});
        }
        return response;
      })
      .catch(async () => {
        // 1. Try to serve from cache if available
        const cachedResponse = await caches.match(event.request);
        if (cachedResponse) {
          return cachedResponse;
        }

        // 2. If it's a page navigation or HTML request, serve fallback or offline page
        if (
          event.request.mode === 'navigate' ||
          (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html'))
        ) {
          const offlinePage = await caches.match('/');
          if (offlinePage) {
            return offlinePage;
          }

          return new Response(
            `<!DOCTYPE html>
            <html lang="fr">
            <head>
              <meta charset="utf-8">
              <meta name="viewport" content="width=device-width, initial-scale=1">
              <title>Service Indisponible - Real Express Voyages</title>
              <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f8fafc; color: #1e293b; text-align: center; padding: 20px; }
                .card { background: #fff; padding: 36px 28px; border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); max-width: 440px; width: 100%; }
                h1 { font-size: 1.3rem; margin-bottom: 12px; color: #e11d48; }
                p { font-size: 0.92rem; color: #64748b; line-height: 1.5; margin-bottom: 22px; }
                .btn { background: #0f172a; color: #fff; text-decoration: none; border: none; padding: 11px 24px; border-radius: 10px; font-weight: 700; cursor: pointer; display: inline-block; }
              </style>
            </head>
            <body>
              <div class="card">
                <h1>Connexion au serveur impossible</h1>
                <p>Impossible de joindre le serveur. Assurez-vous que votre serveur local (php artisan serve ou WAMP) est actif et réessayez.</p>
                <button class="btn" onclick="window.location.reload()">Réessayer</button>
              </div>
            </body>
            </html>`,
            {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({ 'Content-Type': 'text/html; charset=utf-8' })
            }
          );
        }

        // 3. For any other asset, return a valid error Response instead of undefined
        return new Response('', {
          status: 504,
          statusText: 'Gateway Timeout (Offline)'
        });
      })
  );
});
