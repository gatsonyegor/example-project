<template>
    <LoginForm />
</template>

<script setup>
import LoginForm from "~/components/LoginForm.vue";

definePageMeta({
    title: "Вход в систему",
    layout: "center",
    middleware: "loginpass",
});
</script>
