const CACHE_NAME = 'sqt-ictu-v2.1';
var urlsToCache = [
    "",
    "index.html",
    "manifest.json",

    "Assets/styles/mdl.css",
    "Assets/styles/main.css",
    "Assets/images/android-L-Material-Design-Wallpapers-1.png",

    "Assets/fonts/Material-Icons/Material-Icons.css",
    "Assets/fonts/Material-Icons/Material-Icons.woff2",

    "Assets/icons/no-bg/icon.png",

    "Assets/icons/android-icon-144x144.png",
    "Assets/icons/apple-icon-72x72.png",
    "Assets/icons/android-icon-192x192.png",
    "Assets/icons/apple-icon-76x76.png",
    "Assets/icons/android-icon-36x36.png",
    "Assets/icons/apple-icon-precomposed.png",
    "Assets/icons/android-icon-48x48.png",
    "Assets/icons/apple-icon.png",
    "Assets/icons/android-icon-72x72.png",
    "Assets/icons/favicon-16x16.png",
    "Assets/icons/android-icon-96x96.png",
    "Assets/icons/favicon-32x32.png",
    "Assets/icons/apple-icon-114x114.png",
    "Assets/icons/favicon-96x96.png",
    "Assets/icons/apple-icon-120x120.png",
    "Assets/icons/favicon.ico",
    "Assets/icons/apple-icon-144x144.png",
    "Assets/icons/ms-icon-144x144.png",
    "Assets/icons/apple-icon-152x152.png",
    "Assets/icons/ms-icon-150x150.png",
    "Assets/icons/apple-icon-180x180.png",
    "Assets/icons/ms-icon-310x310.png",
    "Assets/icons/apple-icon-57x57.png",
    "Assets/icons/ms-icon-70x70.png",
    "Assets/icons/apple-icon-60x60.png",

    "Assets/vendors/bootstrap-4.1.0/dist/css/bootstrap.min.css",
    "Assets/vendors/fullcalendar-3.9.0/fullcalendar.min.css",
    "Assets/vendors/fontawesome-free-5.0.10/web-fonts-with-css/css/fontawesome-all.min.css",
    "Assets/vendors/mdl/material.teal-purple.min.css",
    "Assets/vendors/jquery-3.3.1.min.js",
    "Assets/vendors/bootstrap-4.1.0/dist/js/bootstrap.min.js",
    "Assets/vendors/moment.js",
    "Assets/vendors/moment-with-locales.js",
    "Assets/vendors/fullcalendar-3.9.0/fullcalendar.min.js",
    "Assets/vendors/fullcalendar-3.9.0/locale-all.js",
    "Assets/vendors/mdl/material.min.js",

    "Assets/scripts/main.js",
];

self.addEventListener('install', function(event) {
    // Perform install steps
    event.waitUntil(
        caches.open(CACHE_NAME)
        .then(function(cache) {
            return cache.addAll(urlsToCache);
        })
    );
});

self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request)
        .then(function(response) {
            // Cache hit - return response
            if (response) {
                return response;
            }

            // IMPORTANT: Clone the request. A request is a stream and
            // can only be consumed once. Since we are consuming this
            // once by cache and once by the browser for fetch, we need
            // to clone the request.
            var fetchRequest = event.request.clone();

            return fetch(fetchRequest).then(
                function(response) {
                    // Check if we received a valid response
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }

                    // IMPORTANT: Clone the response. A response is a stream
                    // and because we want the browser to consume the response
                    // as well as the cache consuming the response, we need
                    // to clone it so we have 2 stream.
                    var responseToCache = response.clone();

                    caches.open(CACHE_NAME)
                        .then(function(cache) {
                            cache.put(event.request, responseToCache);
                        });

                    return response;
                }
            );
        })
    );
});

self.addEventListener('activate', function(event) {

    var cacheWhitelist = [CACHE_NAME];

    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
