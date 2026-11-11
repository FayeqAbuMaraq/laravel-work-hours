<script>
    // ===== تسجيل الـ Service Worker =====
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }

    // ===== زر تثبيت التطبيق (PWA) =====
    let deferredInstallPrompt = null;
    const installButtons = () => document.querySelectorAll('.pwa-install-btn');
    const isStandalone = () =>
        window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const isIos = () => /iphone|ipad|ipod/i.test(navigator.userAgent);

    function showInstallButtons() {
        installButtons().forEach(b => b.classList.remove('hidden'));
    }
    function hideInstallButtons() {
        installButtons().forEach(b => b.classList.add('hidden'));
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredInstallPrompt = e;
        if (!isStandalone()) showInstallButtons();
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        hideInstallButtons();
    });

    document.addEventListener('DOMContentLoaded', () => {
        // iOS Safari ما بيدعم beforeinstallprompt: نعرض الزر مع تعليمات يدوية
        if (isIos() && !isStandalone()) showInstallButtons();

        installButtons().forEach(btn => {
            btn.addEventListener('click', async () => {
                if (deferredInstallPrompt) {
                    deferredInstallPrompt.prompt();
                    await deferredInstallPrompt.userChoice;
                    deferredInstallPrompt = null;
                    hideInstallButtons();
                } else if (isIos()) {
                    document.getElementById('modal-install-ios').classList.remove('hidden');
                }
            });
        });
    });
</script>

{{-- تعليمات التثبيت على iPhone/iPad --}}
<div id="modal-install-ios" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
            <i data-lucide="smartphone" class="w-7 h-7"></i>
        </div>
        <h3 class="font-bold text-lg text-slate-800 mb-2">تثبيت التطبيق على الآيفون</h3>
        <p class="text-sm text-slate-600 leading-7">
            اضغط على زر المشاركة <span class="font-bold">(⬆️)</span> في أسفل المتصفح،
            ثم اختر <span class="font-bold">«إضافة إلى الشاشة الرئيسية»</span>.
        </p>
        <button type="button" onclick="document.getElementById('modal-install-ios').classList.add('hidden')"
                class="mt-5 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-xl transition">
            تمام
        </button>
    </div>
</div>
<script>lucide.createIcons();</script>
