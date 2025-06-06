import '../css/app.css';

import { createInertiaApp, usePage } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import "vue-select/dist/vue-select.css";
import vSelect from 'vue-select'


// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Zilef';

createInertiaApp({
    title: (title : string) : string => `${title} - ${appName}`,
    resolve: (name : string) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }): void {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .component("v-select", vSelect)
            .use(ZiggyVue)
            .mixin({
                methods: {
                    can: function (permissions: any) {
                        const allPermissions = this.$page.props.auth.can;
                        let hasPermission = false;
                        permissions.forEach(function (item: any):void {
                            if (allPermissions[item]) hasPermission = true;
                        });
                        return hasPermission;
                    },
                    lang: function (): string {
                        return usePage()?.props?.language?.original ?? 'es';
                    }
                },
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
