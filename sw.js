// service-worker.js

const CACHE_NAME = 'my-cache-v1'; // キャッシュ名にバージョンを入れる

self.addEventListener('install', (event) => {
  self.skipWaiting(); // 即時反映
});

self.addEventListener('activate', (event) => {
  self.clients.claim(); // 全てのページに即適用

  // 古いキャッシュ削除
  event.waitUntil(
    caches.keys().then((keyList) =>
      Promise.all(
        keyList.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      )
    )
  );
});

// ネットワーク優先、失敗したときだけキャッシュ使用
self.addEventListener('fetch', (event) => {
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});