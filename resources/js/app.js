import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

window.Toastify = Toastify;

window.toast = {
    show(options) {
        const type = options.type || 'info';
        let bg = '#0f172a';
        let borderColor = '#38bdf8';
        let iconSvg = '';

        if (type === 'success') {
            borderColor = '#10b981';
            iconSvg = '<svg class="toast-svg w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        } else if (type === 'error') {
            borderColor = '#ef4444';
            iconSvg = '<svg class="toast-svg w-5 h-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        } else if (type === 'warning') {
            borderColor = '#f59e0b';
            iconSvg = '<svg class="toast-svg w-5 h-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
        } else {
            borderColor = '#38bdf8';
            iconSvg = '<svg class="toast-svg w-5 h-5 text-sky-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        }

        const titleText = options.title || (type === 'success' ? 'Berhasil' : type === 'error' ? 'Gagal' : type === 'warning' ? 'Peringatan' : 'Notifikasi');
        const messageText = options.message || options.text || '';

        const node = document.createElement('div');
        node.className = 'custom-toast-item';
        node.innerHTML = `
            <div class="toast-content-wrapper flex items-start gap-3">
                ${iconSvg}
                <div class="toast-text-box flex-1 min-w-0">
                    <div class="toast-title font-bold text-xs text-white uppercase tracking-wider mb-0.5">${titleText}</div>
                    <div class="toast-message text-xs text-slate-200 leading-snug">${messageText}</div>
                </div>
            </div>
        `;

        Toastify({
            node: node,
            duration: options.duration || 3500,
            close: true,
            gravity: 'top',
            position: 'right',
            stopOnFocus: true,
            className: `app-toast toast-${type}`,
            style: {
                background: bg,
                border: `1px solid ${borderColor}`,
                borderRadius: '10px',
                padding: '12px 14px',
                boxShadow: '0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.2)',
                maxWidth: '420px',
                minWidth: '280px',
            },
            onClick: options.onClick
        }).showToast();
    },
    success(message, title = 'Berhasil') {
        this.show({ type: 'success', message, title });
    },
    error(message, title = 'Gagal') {
        this.show({ type: 'error', message, title });
    },
    warning(message, title = 'Peringatan') {
        this.show({ type: 'warning', message, title });
    },
    info(message, title = 'Informasi') {
        this.show({ type: 'info', message, title });
    }
};
