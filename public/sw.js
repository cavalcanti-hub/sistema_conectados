const CACHE_NAME = "conectados-v20260703-sidebar";

const appUrl = (path = "") => new URL(path, self.registration.scope).toString();

const APP_SHELL = [
  appUrl(""),
  appUrl("index.php"),
  appUrl("assets/css/index.css?v=20260703-sidebar"),
  appUrl("assets/img/logo.png"),
  appUrl("assets/icons/icon-192x192.png"),
  appUrl("assets/icons/icon-512x512.png"),
  appUrl("favicon.png"),
  appUrl("manifest.webmanifest?v=20260703-electric-blue")
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)).then(() => self.skipWaiting())
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key.startsWith("conectados-") && key !== CACHE_NAME).map((key) => caches.delete(key)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (event) => {
  if (event.request.method !== "GET") {
    return;
  }

  const requestUrl = new URL(event.request.url);
  if (requestUrl.protocol !== "http:" && requestUrl.protocol !== "https:") {
    return;
  }

  const isSameOrigin = requestUrl.origin === self.location.origin;
  const isProductMedia =
    requestUrl.pathname.includes("/media/") ||
    requestUrl.pathname.includes("/uploads/") ||
    requestUrl.search.includes("url=media") ||
    requestUrl.search.includes("url=media%2F");

  if (isProductMedia) {
    event.respondWith(fetch(event.request));
    return;
  }

  const acceptHeader = event.request.headers.get("accept") || "";
  const isNavigation =
    event.request.mode === "navigate" ||
    (isSameOrigin && acceptHeader.includes("text/html"));

  if (isNavigation) {
    event.respondWith(
      fetch(event.request, { cache: "no-store" })
        .catch(() => caches.match(event.request).then((cachedResponse) => cachedResponse || caches.match(appUrl("index.php"))))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }

      return fetch(event.request)
        .then((networkResponse) => {
          if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== "basic") {
            return networkResponse;
          }

          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME)
            .then((cache) => cache.put(event.request, responseToCache))
            .catch(() => {});
          return networkResponse;
        })
        .catch(() => cachedResponse || Response.error());
    })
  );
});
