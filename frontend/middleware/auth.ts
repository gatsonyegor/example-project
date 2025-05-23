import { defineNuxtRouteMiddleware, navigateTo, useCookie } from "#app";

const homePage = "/";

export default defineNuxtRouteMiddleware(async (to) => {
    if (to.path.startsWith("/api")) {
        return;
    }

    if (to.path === homePage) {
        return;
    }

    const token = useCookie("jwt");

    if (!token.value) {
        return navigateTo(homePage);
    }

    const isTokenValid = await validateToken(token.value);
    if (!isTokenValid) {
        return navigateTo(homePage);
    }
});
