<template>
    <News />
</template>

<script lang="ts" setup>
import News from "~/components/News.vue";

definePageMeta({
    title: "Новости",
    middleware: "auth",
});
</script>
