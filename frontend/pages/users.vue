<template>
    <Users />
</template>

<script lang="ts" setup>
import Users from "~/components/Users.vue";

definePageMeta({
    title: "Пользователи",
    middleware: "auth",
});
</script>
