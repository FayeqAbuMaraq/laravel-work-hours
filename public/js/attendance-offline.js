/*
 * الحضور والانصراف بدون إنترنت:
 *  - العمليات تُحفظ في IndexedDB بوقت الضغط الفعلي، ثم تُرسل لـ /attendance/sync عند توفر الإنترنت.
 *  - كل سجل مربوط بـ userId حتى لا يُرفع لحساب مستخدم آخر على نفس الجهاز.
 *  - الملف يُحمَّل في كل الصفحات للمزامنة التلقائية، وصفحة /attendance تستدعي AttendanceOffline.mountPage().
 */
(function () {
    'use strict';

    const DB_NAME = 'work-hours-offline';
    const STORE = 'sessions';
    const userMeta = document.querySelector('meta[name="user-id"]');
    const USER_ID = userMeta ? userMeta.content : null;

    // ---------- IndexedDB ----------
    function openDb() {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open(DB_NAME, 1);
            req.onupgradeneeded = () => req.result.createObjectStore(STORE, { keyPath: 'uuid' });
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }

    async function dbGetAll() {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const req = db.transaction(STORE, 'readonly').objectStore(STORE).getAll();
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }

    async function dbPut(record) {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readwrite');
            tx.objectStore(STORE).put(record);
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    async function dbDelete(uuids) {
        if (!uuids.length) return;
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readwrite');
            uuids.forEach(u => tx.objectStore(STORE).delete(u));
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    async function mine() {
        const all = await dbGetAll();
        return all.filter(r => String(r.userId) === String(USER_ID));
    }

    function uuid() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }

    // ---------- المزامنة ----------
    let syncing = false;

    async function syncPending() {
        if (!USER_ID || syncing || !navigator.onLine) return 0;
        syncing = true;
        try {
            const pending = await mine();
            if (!pending.length) return 0;

            const tokenRes = await fetch('/attendance/token', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!tokenRes.ok) return 0; // غالباً الجلسة منتهية: نبقي البيانات لحد ما يسجل دخول
            const { token } = await tokenRes.json();

            const res = await fetch('/attendance/sync', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    sessions: pending.map(r => ({ uuid: r.uuid, started_at: r.started_at, ended_at: r.ended_at })),
                }),
            });
            if (!res.ok) return 0;

            const data = await res.json();
            await dbDelete([...(data.synced || []), ...(data.rejected || [])]);

            if (data.rejected && data.rejected.length && typeof ajaxToast === 'function') {
                ajaxToast('تعذّرت مزامنة ' + data.rejected.length + ' عملية (وقت غير صالح)، تحقق من ساعة جهازك', 'error');
            }
            const n = (data.synced || []).length;
            if (n > 0) {
                if (typeof ajaxToast === 'function') ajaxToast('تمت مزامنة ' + n + ' عملية');
                document.dispatchEvent(new CustomEvent('attendance:synced', { detail: { count: n } }));
            }
            return n;
        } catch (e) {
            return 0; // خطأ شبكة: نحاول لاحقاً
        } finally {
            syncing = false;
        }
    }

    // ---------- شريط "غير متصل" ----------
    function updateBanner() {
        const el = document.getElementById('offline-banner');
        if (el) el.classList.toggle('hidden', navigator.onLine);
    }

    window.addEventListener('online', () => { updateBanner(); syncPending(); });
    window.addEventListener('offline', updateBanner);
    document.addEventListener('DOMContentLoaded', () => { updateBanner(); syncPending(); });
    setInterval(syncPending, 30000);

    // ---------- صفحة /attendance ----------
    function mountPage(cfg) {
        const root = document.getElementById('att-root');
        if (!root) return;

        const serverOpen = cfg.serverOpen; // {uuid, started_at} | null
        let timerId = null;

        const pad = n => String(n).padStart(2, '0');
        const fmtTime = iso => new Date(iso).toLocaleTimeString('ar-EG', { hour: 'numeric', minute: '2-digit' });
        const fmtDate = iso => new Date(iso).toLocaleDateString('ar-EG', { year: 'numeric', month: 'short', day: 'numeric' });

        async function currentOpen() {
            const list = await mine();
            const localOpen = list.find(r => !r.ended_at);
            if (localOpen) return localOpen;
            if (serverOpen && !list.some(r => r.uuid === serverOpen.uuid)) return serverOpen;
            return null;
        }

        async function render() {
            const open = await currentOpen();
            const pending = (await mine()).length;
            clearInterval(timerId);

            const pendingHtml = pending
                ? `<p class="mt-4 text-xs text-amber-700 bg-amber-50 rounded-xl px-3 py-2">عندك ${pending} عملية بانتظار المزامنة${navigator.onLine ? '...' : ' (بتتزامن لما يرجع الإنترنت)'}</p>`
                : '';

            if (open) {
                root.innerHTML = `
                    <div class="text-center">
                        <span class="relative inline-flex h-3 w-3 mb-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <p class="font-bold text-lg">أنت الآن على رأس عملك</p>
                        <p class="text-sm text-slate-500 mt-1">دخلت ${fmtDate(open.started_at)} - ${fmtTime(open.started_at)}</p>
                        <p id="att-timer" class="text-4xl font-extrabold text-slate-800 my-5" dir="ltr">00:00:00</p>
                        <button id="att-out" type="button" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-xl transition flex items-center justify-center gap-2">
                            <i data-lucide="log-out" class="w-5 h-5"></i> تسجيل خروج
                        </button>
                        ${pendingHtml}
                    </div>`;
                const start = new Date(open.started_at).getTime();
                const tick = () => {
                    const s = Math.max(0, Math.floor((Date.now() - start) / 1000));
                    const t = document.getElementById('att-timer');
                    if (t) t.textContent = `${pad(Math.floor(s / 3600))}:${pad(Math.floor(s % 3600 / 60))}:${pad(s % 60)}`;
                };
                tick();
                timerId = setInterval(tick, 1000);
                document.getElementById('att-out').addEventListener('click', checkOut);
            } else {
                root.innerHTML = `
                    <div class="text-center">
                        <div class="inline-flex bg-emerald-50 text-emerald-600 p-3 rounded-2xl mb-3"><i data-lucide="log-in" class="w-7 h-7"></i></div>
                        <p class="font-bold text-lg">تسجيل الحضور</p>
                        <p class="text-sm text-slate-500 mt-1 mb-5">اضغط عند بدء العمل، حتى بدون إنترنت</p>
                        <button id="att-in" type="button" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl transition flex items-center justify-center gap-2">
                            <i data-lucide="log-in" class="w-5 h-5"></i> تسجيل دخول
                        </button>
                        ${pendingHtml}
                    </div>`;
                document.getElementById('att-in').addEventListener('click', checkIn);
            }
            if (window.lucide) lucide.createIcons();
        }

        async function checkIn() {
            if (await currentOpen()) return;
            await dbPut({ uuid: uuid(), userId: USER_ID, started_at: new Date().toISOString(), ended_at: null });
            if (typeof ajaxToast === 'function') ajaxToast(navigator.onLine ? 'تم تسجيل دخولك' : 'تم تسجيل دخولك (محفوظ على الجهاز)');
            await render();
            syncPending();
        }

        async function checkOut() {
            const open = await currentOpen();
            if (!open) return;
            const endedAt = new Date().toISOString();
            // الجلسة قد تكون محلية، أو مفتوحة على السيرفر من قبل (ننشئ لها سجل محلي بنفس الـ uuid)
            await dbPut({ uuid: open.uuid, userId: USER_ID, started_at: open.started_at, ended_at: endedAt });
            if (typeof ajaxToast === 'function') ajaxToast(navigator.onLine ? 'تم تسجيل خروجك' : 'تم تسجيل خروجك (محفوظ على الجهاز)');
            await render();
            syncPending();
        }

        // بعد نجاح المزامنة نعيد تحميل الصفحة لتحديث حالة السيرفر
        document.addEventListener('attendance:synced', () => setTimeout(() => location.reload(), 600));
        window.addEventListener('online', render);
        window.addEventListener('offline', render);

        render();
    }

    window.AttendanceOffline = { syncPending, mountPage };
})();
