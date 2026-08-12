import { createInertiaApp, router } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

createInertiaApp({
    title: (title, page) => {
        const appName = String(page.props.name || 'Spendora');

        return title ? `${title} - ${appName}` : appName;
    },
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name === 'settings/Profile':
                return AppLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();

function syncDocumentLocale(): void {
    const locale = document.documentElement.lang || 'en';
    document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
}

router.on('navigate', (event) => {
    const locale = String(event.detail.page.props.locale ?? 'en');
    document.documentElement.lang = locale;
    syncDocumentLocale();
});

syncDocumentLocale();
