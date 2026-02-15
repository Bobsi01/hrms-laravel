import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * HRMS Global JavaScript
 * Migrated from assets/js/app.js (core features)
 */

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initUserMenu();
    initNotifications();
    initConfirmModal();
    initFlashNotifications();
    initClock();
});

/* ── Sidebar Collapse ──────────────────────────────────────── */
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const btnCollapse = document.getElementById('btnCollapse');
    if (!sidebar || !btnCollapse) return;

    const stored = localStorage.getItem('sidebar_collapsed');
    if (stored === '1') sidebar.classList.add('collapsed');

    btnCollapse.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
    });

    // Nav group toggle
    document.querySelectorAll('[data-group-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            const groupId = btn.getAttribute('data-group-toggle');
            const content = btn.closest('.nav-group')?.querySelector('.group-content');
            if (!content) return;
            content.classList.toggle('is-collapsed');
            const expanded = !content.classList.contains('is-collapsed');
            btn.setAttribute('aria-expanded', expanded);
            const arrow = btn.querySelector('svg');
            if (arrow) arrow.style.transform = expanded ? '' : 'rotate(-90deg)';
        });
    });
}

/* ── User Dropdown ────────────────────────────────────────── */
function initUserMenu() {
    const btnUser = document.getElementById('btnUser');
    const userMenu = document.getElementById('userMenu');
    if (!btnUser || !userMenu) return;

    btnUser.addEventListener('click', (e) => {
        e.stopPropagation();
        userMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!userMenu.contains(e.target) && !btnUser.contains(e.target)) {
            userMenu.classList.add('hidden');
        }
    });
}

/* ── Notification Bell ────────────────────────────────────── */
function initNotifications() {
    const btnNotif = document.getElementById('btnNotif');
    const dropdown = document.getElementById('notifDropdown');
    if (!btnNotif || !dropdown) return;

    btnNotif.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            loadNotifications();
        }
    });

    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target) && !btnNotif.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

async function loadNotifications() {
    const btn = document.getElementById('btnNotif');
    const feedUrl = btn?.dataset.feedUrl;
    if (!feedUrl) return;

    try {
        const resp = await fetch(feedUrl, { headers: { 'Accept': 'application/json' } });
        if (!resp.ok) return;
        const data = await resp.json();
        const container = document.getElementById('notifItems');
        const empty = document.getElementById('notifEmpty');
        const badge = document.querySelector('[data-notif-badge]');

        if (!container) return;

        if (data.notifications && data.notifications.length > 0) {
            container.innerHTML = data.notifications.map(n => `
                <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer ${!n.is_read ? 'bg-indigo-50/40' : ''}">
                    <div class="text-sm font-medium text-slate-900">${escapeHtml(n.title)}</div>
                    <div class="text-xs text-slate-500 mt-0.5">${escapeHtml(n.message)}</div>
                    <div class="text-[10px] text-slate-400 mt-1">${n.time_ago}</div>
                </div>
            `).join('');
            empty?.classList.add('hidden');
        } else {
            container.innerHTML = '';
            empty?.classList.remove('hidden');
        }

        if (badge && data.unread_count > 0) {
            badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
            badge.classList.remove('hidden');
        } else if (badge) {
            badge.classList.add('hidden');
        }
    } catch (e) {
        console.error('Failed to load notifications', e);
    }
}

/* ── Confirm Modal ────────────────────────────────────────── */
function initConfirmModal() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-confirm]');
        if (!trigger) return;

        e.preventDefault();
        e.stopPropagation();

        const message = trigger.getAttribute('data-confirm') || 'Are you sure?';
        const modal = document.getElementById('confirmModal');
        const msgEl = document.getElementById('confirmMessage');
        const yesBtn = document.getElementById('confirmYes');

        if (!modal || !yesBtn) return;

        msgEl.textContent = message;
        modal.classList.remove('hidden');

        const handler = () => {
            modal.classList.add('hidden');
            yesBtn.removeEventListener('click', handler);

            if (trigger.tagName === 'A') {
                window.location.href = trigger.href;
            } else if (trigger.form) {
                trigger.form.submit();
            } else if (trigger.closest('form')) {
                trigger.closest('form').submit();
            }
        };
        yesBtn.addEventListener('click', handler);
    });

    // Close confirm modal
    document.querySelectorAll('[data-confirm-close]').forEach(el => {
        el.addEventListener('click', () => {
            document.getElementById('confirmModal')?.classList.add('hidden');
        });
    });
}

/* ── Flash Notifications Auto-close ──────────────────────── */
function initFlashNotifications() {
    document.querySelectorAll('.notif[data-autoclose]').forEach(notif => {
        const timeout = parseInt(notif.dataset.timeout) || 5000;
        setTimeout(() => {
            notif.style.opacity = '0';
            notif.style.transform = 'translateY(-8px)';
            setTimeout(() => notif.remove(), 300);
        }, timeout);

        const closeBtn = notif.querySelector('[data-close]');
        closeBtn?.addEventListener('click', () => {
            notif.style.opacity = '0';
            setTimeout(() => notif.remove(), 200);
        });
    });
}

/* ── Header Clock ────────────────────────────────────────── */
function initClock() {
    const clock = document.getElementById('headerClock');
    if (!clock) return;

    function update() {
        const now = new Date();
        clock.textContent = now.toLocaleString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric',
            hour: 'numeric', minute: '2-digit', second: '2-digit',
            hour12: true, timeZone: 'Asia/Manila'
        });
    }
    update();
    setInterval(update, 1000);
}

/* ── Utility ──────────────────────────────────────────────── */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Expose globally
window.escapeHtml = escapeHtml;
