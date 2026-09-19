import * as bootstrap from 'bootstrap';
import './theme.js';

window.bootstrap = bootstrap;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
let revealObserver;
let orbitObserver;
let userPausedMotion = false;
try { userPausedMotion = localStorage.getItem('mk-reduce-motion') === 'true'; } catch (_) { /* Keep an in-memory preference. */ }

function setupMotion() {
    revealObserver?.disconnect();
    orbitObserver?.disconnect();
    const paused = reducedMotion.matches || userPausedMotion;
    document.documentElement.dataset.motion = paused ? 'reduced' : 'full';
    document.querySelectorAll('[data-motion-toggle]').forEach(button => {
        button.hidden = false;
        button.disabled = reducedMotion.matches;
        button.setAttribute('aria-pressed', String(paused));
        button.title = reducedMotion.matches ? 'Animasi dinonaktifkan sesuai pengaturan perangkat' : paused ? 'Lanjutkan animasi' : 'Jeda animasi';
        const icon = button.querySelector('i');
        if (icon) icon.className = paused ? 'bi bi-play' : 'bi bi-pause';
        if (button.dataset.motionBound) return;
        button.dataset.motionBound = 'true';
        button.addEventListener('click', () => {
            userPausedMotion = !userPausedMotion;
            try { localStorage.setItem('mk-reduce-motion', String(userPausedMotion)); } catch (_) { /* Storage is optional. */ }
            setupMotion();
        });
    });
    const elements = [...document.querySelectorAll('[data-scroll-reveal]')];
    document.documentElement.classList.add('mk-scroll-reveal-enabled');

    if (paused || !('IntersectionObserver' in window)) {
        elements.forEach(element => element.classList.add('is-in-view'));
        return;
    }
    revealObserver = new IntersectionObserver(entries => {
        entries.forEach(({ target, isIntersecting }) => {
            target.classList.toggle('is-in-view', isIntersecting || target.contains(document.activeElement));
        });
    }, { threshold: 0.01, rootMargin: '-3% 0px -3%' });
    elements.forEach(element => revealObserver.observe(element));

    orbitObserver = new IntersectionObserver(entries => {
        entries.forEach(({ target, isIntersecting }) => target.classList.toggle('mk-motion-paused', !isIntersecting));
    });
    document.querySelectorAll('.mk-hero-motion').forEach(element => orbitObserver.observe(element));
}

function setupControls() {
    document.querySelectorAll('.nav-link.active, .mk-profile-nav-button.active').forEach(link => link.setAttribute('aria-current', 'page'));
    document.querySelectorAll('input[type="password"]').forEach(input => {
        if (input.closest('.mk-password-field') || !input.id) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'mk-password-field';
        input.before(wrapper);
        wrapper.append(input);
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'mk-password-toggle';
        button.setAttribute('aria-controls', input.id);
        button.setAttribute('aria-label', 'Tampilkan password');
        button.setAttribute('aria-pressed', 'false');
        const icon = document.createElement('i');
        icon.className = 'bi bi-eye';
        icon.setAttribute('aria-hidden', 'true');
        button.append(icon);
        button.addEventListener('click', () => {
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
            button.setAttribute('aria-pressed', String(visible));
            icon.className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
        wrapper.append(button);
    });
}

let pendingConfirmation;
const confirmedForms = new WeakSet();
function setupDialog() {
    const dialog = document.querySelector('[data-confirm-dialog]');
    if (!dialog || dialog.dataset.bound) return;
    dialog.dataset.bound = 'true';
    const password = dialog.querySelector('[data-confirm-password-input]');
    dialog.querySelector('[data-confirm-cancel]').addEventListener('click', () => dialog.close('cancel'));
    dialog.querySelector('[data-confirm-accept]').addEventListener('click', () => {
        if (!pendingConfirmation || (password.required && !password.reportValidity())) return;
        const { form, submitter } = pendingConfirmation;
        if (password.required) {
            let field = form.querySelector('input[name="admin_password"]');
            if (!field) {
                field = document.createElement('input');
                field.type = 'hidden';
                field.name = 'admin_password';
                field.dataset.confirmCredential = 'true';
                form.append(field);
            }
            field.value = password.value;
        }
        pendingConfirmation = null;
        dialog.close('confirm');
        confirmedForms.add(form);
        try {
            submitter ? form.requestSubmit(submitter) : form.requestSubmit();
        } finally {
            confirmedForms.delete(form);
            password.value = '';
            form.querySelector('[data-confirm-credential]')?.remove();
        }
    });
    dialog.addEventListener('close', () => {
        pendingConfirmation = null;
        password.value = '';
        password.type = 'password';
        const toggle = password.parentElement.querySelector('.mk-password-toggle');
        toggle?.setAttribute('aria-label', 'Tampilkan password');
        toggle?.setAttribute('aria-pressed', 'false');
        if (toggle) toggle.querySelector('i').className = 'bi bi-eye';
    });
}

// Delegation also covers forms rendered after a Livewire update.
document.addEventListener('submit', event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;
    if (form.hasAttribute('data-confirm') && !confirmedForms.has(form)) {
        const dialog = document.querySelector('[data-confirm-dialog]');
        if (dialog && typeof dialog.showModal === 'function') {
            event.preventDefault();
            if (dialog.open) return;
            pendingConfirmation = { form, submitter: event.submitter };
            dialog.querySelector('[data-confirm-dialog-title]').textContent = form.dataset.confirmTitle || 'Konfirmasi tindakan';
            dialog.querySelector('[data-confirm-dialog-message]').textContent = form.dataset.confirm;
            dialog.querySelector('[data-confirm-accept]').textContent = form.dataset.confirmAction || 'Lanjutkan';
            dialog.dataset.tone = form.dataset.confirmTone || 'warning';
            const needsPassword = form.hasAttribute('data-confirm-password');
            dialog.querySelector('[data-confirm-password-section]').hidden = !needsPassword;
            const password = dialog.querySelector('[data-confirm-password-input]');
            password.required = needsPassword;
            password.value = '';
            dialog.showModal();
            dialog.querySelector('[data-confirm-cancel]').focus();
            return;
        }
        // Unsupported browsers cannot bypass the server's password check.
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
            return;
        }
    }
    if (form.method.toLowerCase() === 'get' || [...form.attributes].some(a => a.name.startsWith('wire:submit'))) return;
    if (form.getAttribute('aria-busy') === 'true') {
        event.preventDefault();
        return;
    }
    form.setAttribute('aria-busy', 'true');
    // Preserve submitter name/value during the browser's native submission.
    queueMicrotask(() => {
        form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach(button => {
            if (!button.disabled) {
                button.disabled = true;
                button.dataset.submitDisabled = 'true';
            }
        });
    });
});

function resetBusyForms() {
    document.querySelectorAll('form[aria-busy="true"]').forEach(form => form.removeAttribute('aria-busy'));
    document.querySelectorAll('[data-submit-disabled]').forEach(button => {
        button.disabled = false;
        delete button.dataset.submitDisabled;
    });
}
function setup() { setupMotion(); setupControls(); setupDialog(); resetBusyForms(); }
document.addEventListener('DOMContentLoaded', setup);
document.addEventListener('DOMContentLoaded', () => document.querySelector('[data-validation-summary]')?.focus());
document.addEventListener('livewire:navigated', setup);
document.addEventListener('focusin', event => event.target.closest('[data-scroll-reveal]')?.classList.add('is-in-view'));
document.addEventListener('visibilitychange', () => document.documentElement.classList.toggle('mk-document-hidden', document.hidden));
window.addEventListener('pageshow', resetBusyForms);
reducedMotion.addEventListener('change', setupMotion);
window.addEventListener('storage', event => {
    if (event.key === 'mk-reduce-motion') {
        userPausedMotion = event.newValue === 'true';
        setupMotion();
    }
});
