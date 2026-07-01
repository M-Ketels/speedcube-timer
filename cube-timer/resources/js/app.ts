import { createInertiaApp, router } from '@inertiajs/vue3'; // Added router
import { createApp, h } from 'vue'; // Added Vue imports
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

import themesData from './themes.json';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },

    setup({ el, App, props, plugin }) {

        const applyTheme = (themeName: string) => {
            const themeConfig = themesData[themeName as keyof typeof themesData] || themesData['default_dark'];

            Object.entries(themeConfig.colors).forEach(([cssVariable, value]) => {
                document.documentElement.style.setProperty(cssVariable, value);
            });

            document.documentElement.style.setProperty('--sidebar-background', themeConfig.colors['--card']);
            document.documentElement.style.setProperty('--sidebar-border', themeConfig.colors['--border']);
            document.documentElement.style.setProperty('--sidebar-foreground', themeConfig.colors['--foreground']);
            document.documentElement.style.setProperty('--sidebar-accent', themeConfig.colors['--muted']);
            document.documentElement.style.setProperty('--sidebar-accent-foreground', themeConfig.colors['--primary']);
            document.documentElement.style.setProperty('--sidebar-ring', themeConfig.colors['--ring']);
        };
        applyTheme(props.initialPage.props.auth?.user?.theme_name);

        router.on('navigate', (event) => {
            applyTheme(event.detail.page.props.auth?.user?.theme_name);
        });

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
