import tailwindcss from "@tailwindcss/vite";
import Lara from "@primeuix/themes/lara";
import { definePreset } from "@primeuix/themes";

const MyPreset = definePreset(Lara, {
    //Your customizations, see the following sections for examples
    semantic: {
        primary: {
            50: "{indigo.50}",
            100: "{indigo.100}",
            200: "{indigo.200}",
            300: "{indigo.300}",
            400: "{indigo.400}",
            500: "{indigo.500}",
            600: "{indigo.600}",
            700: "{indigo.700}",
            800: "{indigo.800}",
            900: "{indigo.900}",
            950: "{indigo.950}",
        },
    },
});

export default defineNuxtConfig({
    compatibilityDate: "2024-11-01",
    devtools: { enabled: true },
    ssr: false,
    modules: ["@primevue/nuxt-module"],
    css: ["~/assets/css/main.css"],
    app: {
        baseURL: '/'
    },
    typescript: {
        typeCheck: true,
    },
    primevue: {
        options: {
            theme: {
                preset: MyPreset,
                options: {
                    darkModeSelector: ".my-selector",
                },
            },
        },
    },
    vite: { plugins: [tailwindcss()] },
    runtimeConfig: {
        public: {
            apiBase: 'http://localhost',
        },
    },
});
