import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * ======================================
 * コマンドパレット用ショートカット
 * Ctrl / Cmd + K, Ctrl / Cmd + /
 * ======================================
 */
window.addEventListener(
    'keydown',
    (e) => {
        const key = (e.key || '').toLowerCase();

        // 開く
        if ((e.ctrlKey || e.metaKey) && (key === 'k' || key === '/')) {
            e.preventDefault();
            window.dispatchEvent(new Event('open-command-palette'));
        }

        // 閉じる
        if (key === 'escape') {
            window.dispatchEvent(new Event('close-command-palette'));
        }
    },
    { capture: true }
);

Alpine.start();
