// Service Worker:
//  1) يخلّي الموقع قابل للتثبيت (PWA).
//  2) صفحة /attendance (بدون أي أرقام مالية) تُخزَّن بالكاش لتعمل بدون إنترنت.
//  3) ملفات الواجهة (Tailwind/Lucide/الخطوط/JS) بالكاش لتظهر الصفحة بشكلها الكامل أوفلاين.
// باقي صفحات المستخدم (الرواتب والمسحوبات) ما بتتخزن أبداً: بيانات مالية ومرتبطة بالجلسة.
// ملاحظة: الصفحة المخزّنة تُمسح عند تسجيل الخروج (caches.delete في زر الخروج).
const STATIC = 'work-hours-static-v2';
const PAGES = 'work-hours-pages-v1';
const OFFLINE_URL = '/offline.html';
const PRECACHE = [OFFLINE_URL, '/icons/icon-192.png', '/icons/icon-512.png', '/js/attendance-offline.js?v=1'];
const CDN_HOSTS = ['cdn.tailwindcss.com', 'unpkg.com', 'fonts.googleapis.com', 'fonts.gstatic.com'];

self.addEventListener('install', (event) => {
    // نخزّن كل ملف على حدة: لو ملف واحد فشل (مفقود أو رجّعت الاستضافة صفحة غير متوقعة)
    // ما نفشل تثبيت الـ service worker كله (addAll كانت تفشل بالكامل لو ملف واحد فشل).
    event.waitUntil(
        caches.open(STATIC).then((cache) =>
            Promise.all(PRECACHE.map((url) => cache.add(url).catch(() => null)))
        )
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== STATIC && k !== PAGES).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

function isCacheable(res) {
    return res && (res.ok || res.type === 'opaque');
}

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // ----- تنقّل بين الصفحات -----
    if (req.mode === 'navigate') {
        if (url.origin === location.origin && url.pathname === '/attendance') {
            // الشبكة أولاً، ونحفظ نسخة (إلا لو حوّلنا لصفحة الدخول)، وأوفلاين نرجّع النسخة المخزنة
            event.respondWith(
                fetch(req)
                    .then((res) => {
                        if (res.ok && !res.redirected && res.type === 'basic') {
                            const copy = res.clone();
                            caches.open(PAGES).then((c) => c.put('/attendance', copy));
                        }
                        return res;
                    })
                    .catch(() => caches.match('/attendance', { cacheName: PAGES })
                        .then((hit) => hit || caches.match(OFFLINE_URL)))
            );
            return;
        }
        // نقاط الدخول (الرئيسية، أيام العمل، الدخول): أوفلاين نعرض صفحة الحضور المخزّنة إن وُجدت
        const entry = ['/', '/work-days', '/login'].includes(url.pathname);
        event.respondWith(
            fetch(req).catch(() =>
                (entry ? caches.match('/attendance', { cacheName: PAGES }) : Promise.resolve(null))
                    .then((hit) => hit || caches.match(OFFLINE_URL))
            )
        );
        return;
    }

    // ----- ملفات الواجهة: كاش أولاً مع تحديث بالخلفية -----
    const sameOriginStatic = url.origin === location.origin &&
        (url.pathname.startsWith('/js/') || url.pathname.startsWith('/icons/') || url.pathname === '/manifest.webmanifest');
    const cdn = CDN_HOSTS.includes(url.hostname);

    if (sameOriginStatic || cdn) {
        event.respondWith(
            caches.open(STATIC).then((cache) =>
                cache.match(req).then((hit) => {
                    const network = fetch(req)
                        .then((res) => {
                            if (isCacheable(res)) cache.put(req, res.clone());
                            return res;
                        })
                        .catch(() => hit);
                    return hit || network;
                })
            )
        );
    }
});