import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Track PageView on SPA transitions for Meta Pixel & Google Analytics
router.on('navigate', () => {
    if (typeof window.fbq === 'function') {
        window.fbq('track', 'PageView');
    }
    if (typeof window.gtag === 'function') {
        window.gtag('event', 'page_view', {
            page_path: window.location.pathname + window.location.search,
        });
    }
});

createInertiaApp({
    title: (title) => `${title}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mixin({
                methods: {
                    __(key, replacements = {}) {
                        let translation = this.$page.props.translations[key] || key;

                        Object.keys(replacements).forEach(r => {
                            translation = translation.replace(`:${r}`, replacements[r]);
                        });

                        return translation;
                    }
                }
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

