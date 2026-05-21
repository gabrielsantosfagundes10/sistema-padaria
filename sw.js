const CACHE_NAME = 'padaria-v3'; // Mudei para v3 para forçar o navegador a atualizar

// 1. Lista de arquivos para cache (Offline)
const STATIC_ASSETS = [
  './', // Raiz
  'index.php',
  'manifest.json',
  'icon.png',
  'offline.html'
];

// INSTALAÇÃO: Salva os arquivos essenciais no cache
self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return Promise.all(
        STATIC_ASSETS.map(asset => {
          return cache.add(asset).catch(err => console.log("Erro ao cachear:", asset));
        })
      );
    })
  );
});

// ATIVAÇÃO: Limpa caches antigos e assume o controle imediatamente
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(name => name !== CACHE_NAME).map(name => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

// BUSCA (FETCH): Gerencia o que carregar quando está online ou offline
self.addEventListener('fetch', event => {
  const req = event.request;

  // Ignora extensões do Chrome e pedidos externos para não travar o log
  if (!req.url.startsWith(self.location.origin)) return;

  // Lógica para páginas PHP (onde o sistema funciona)
  if (req.mode === 'navigate' || req.url.includes('.php')) {
    event.respondWith(
      fetch(req).catch(() => {
        return caches.match('offline.html') || caches.match('./');
      })
    );
    return;
  }

  // Lógica para arquivos estáticos (CSS, imagens, etc)
  event.respondWith(
    caches.match(req).then(cached => {
      return cached || fetch(req);
    })
  );
});