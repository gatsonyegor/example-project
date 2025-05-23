import { defineNuxtRouteMiddleware, navigateTo, useCookie } from "#app";

const newsPage = "/news";

export default defineNuxtRouteMiddleware(async (to) => {
    if (import.meta.server) return;

    const token = useCookie("jwt");

    if (!token.value) {
        return;
    }

    const isTokenValid = await validateToken(token.value);
    if (isTokenValid) {
        return navigateTo(newsPage);
    }
});
