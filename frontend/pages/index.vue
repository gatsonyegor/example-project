<template>
    <LoginForm />
</template>

<script setup>
import LoginForm from "~/components/LoginForm.vue";

definePageMeta({
    layout: "center",
    middleware: "loginpass",
});
</script>
