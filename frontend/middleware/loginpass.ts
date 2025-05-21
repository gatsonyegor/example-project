import { defineNuxtRouteMiddleware, navigateTo, useCookie } from "#app";

const newsPage = "/news";

export default defineNuxtRouteMiddleware(async () => {
    if (import.meta.server) return;

    const token = useCookie("jwt");

    if (!token.value) {
        return;
    }

    return navigateTo(newsPage);
});
