import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/inertia-vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import FloatingVue from "floating-vue";
import "floating-vue/dist/style.css";
import { plugin, defaultConfig } from "@formkit/vue";
import "@formkit/themes/genesis";

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin: inertiaPlugin }) {
        createApp({ render: () => h(App, props) })
            .use(inertiaPlugin)
            .use(FloatingVue)
            .use(plugin, defaultConfig)
            .mount(el);
    },
});
