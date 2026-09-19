const STORAGE_KEY = 'mk-theme';

function normalizedTheme(theme) {
    return theme === 'dark' ? 'dark' : 'light';
}

function applyTheme(theme) {
    const nextTheme = normalizedTheme(theme);
    document.documentElement.setAttribute('data-theme', nextTheme);
    document.documentElement.setAttribute('data-bs-theme', nextTheme);

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const darkModeIsActive = nextTheme === 'dark';
        const nextLabel = darkModeIsActive ? 'Aktifkan tema terang' : 'Aktifkan tema gelap';
        const icon = button.querySelector('[data-theme-icon]');

        button.setAttribute('aria-pressed', String(darkModeIsActive));
        button.setAttribute('aria-label', nextLabel);
        button.setAttribute('title', nextLabel);

        if (icon) {
            icon.className = darkModeIsActive ? 'bi bi-sun' : 'bi bi-moon-stars';
        }
    });
}

function currentTheme() {
    try { return normalizedTheme(localStorage.getItem(STORAGE_KEY)); }
    catch (_) { return normalizedTheme(document.documentElement.dataset.theme); }
}

function bindThemeControls() {
    applyTheme(currentTheme());

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        if (button.dataset.themeBound === 'true') {
            return;
        }

        button.dataset.themeBound = 'true';
        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            try { localStorage.setItem(STORAGE_KEY, nextTheme); } catch (_) { /* Keep the theme for this page. */ }
            applyTheme(nextTheme);
        });
    });
}

document.addEventListener('DOMContentLoaded', bindThemeControls);
document.addEventListener('livewire:navigated', bindThemeControls);
window.addEventListener('storage', (event) => {
    if (event.key === STORAGE_KEY) {
        applyTheme(event.newValue);
    }
});
