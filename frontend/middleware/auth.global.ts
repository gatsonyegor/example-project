import {
  defineNuxtRouteMiddleware,
  navigateTo,
  useCookie,
} from "#app";

export default defineNuxtRouteMiddleware(async (to) => {
  const token = useCookie("jwt");

  if (!token.value) {
    if (to.path === "/login") {
      return;
    }
    return navigateTo("/login");
  }

  const response = await fetch(
    "http://localhost/api/auth/validate-token",
    {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${token.value}`,
      },
    }
  );

  const data = await response.json();

  if (data.code !== undefined && data.code !== 200) {
    return navigateTo("/login");
  }

  if (data.error) {
    if (to.path === "/login") {
        return;
    }
    return navigateTo("/login");
  }

  if (to.path === "/login") {
    return navigateTo("/");
  }
});
