const CACHE_NAME = "conectados-v20260704-pwa-install-fix";

const appUrl = (path = "") => new URL(path, self.registration.scope).toString();

const APP_SHELL = [
  appUrl(""),
  appUrl("vitrine"),
  appUrl("vitrine?pwa=1"),
  appUrl("assets/css/index.css?v=20260703-compact-blue"),
  appUrl("assets/img/logo.png"),
  appUrl("assets/icons/icon-192x192.png"),
  appUrl("assets/icons/icon-512x512.png"),
  appUrl("favicon.png"),
  appUrl("manifest.webmanifest?v=20260704-pwa-install-fix")
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => Promise.all(APP_SHELL.map((url) =>
        fetch(url, { cache: "reload" })
          .then((response) => response.ok ? cache.put(url, response) : Promise.resolve())
          .catch(() => Promise.resolve())
      )))
      .then(() => self.skipWaiting())
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
        .catch(() => caches.match(event.request)
          .then((fallbackResponse) =>
            fallbackResponse ||
            caches.match(appUrl("vitrine?pwa=1")) ||
            caches.match(appUrl("vitrine")) ||
            caches.match(appUrl(""))
          )
          .then((fallbackResponse) => fallbackResponse || new Response("<!doctype html><title>Conectados</title><p>Recurso temporariamente indisponivel.</p>", {
            status: 200,
            headers: { "Content-Type": "text/html; charset=utf-8" }
          }))
        )
    );
    return;
  }

  function offlineFallback(request) {
    if (request.destination === "style") {
      return new Response("", {
        status: 200,
        headers: { "Content-Type": "text/css; charset=utf-8" }
      });
    }

    if (request.destination === "script") {
      return new Response("", {
        status: 200,
        headers: { "Content-Type": "application/javascript; charset=utf-8" }
      });
    }

    if (request.destination === "image") {
      return new Response(
        '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"></svg>',
        { status: 200, headers: { "Content-Type": "image/svg+xml; charset=utf-8" } }
      );
    }

    return new Response("", {
      status: 200,
      headers: { "Content-Type": "text/plain; charset=utf-8" }
    });
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
        .catch(() => cachedResponse || offlineFallback(event.request));
    })
  );
});
