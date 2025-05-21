import { defineNuxtRouteMiddleware, navigateTo, useCookie } from "#app";

const homePage = "/";

export default defineNuxtRouteMiddleware(async (to) => {
    if (to.path === homePage) {
        return;
    }

    const token = useCookie("jwt");

    if (!token.value) {
        return navigateTo(homePage);
    }
});
