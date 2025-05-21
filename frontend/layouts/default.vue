<template>
    <NotificationList />
    <div class="container mx-auto px-4 py-8">
        <div class="w-full grid grid-cols-12 gap-8 mb-8">
            <div class="col-span-12">
                <HeaderMenu />
            </div>
        </div>

        <div class="w-full grid grid-cols-12 gap-8">
            <slot />
        </div>
    </div>
</template>

<script lang="ts">
import HeaderMenu from "../components/HeaderMenu.vue";
import NotificationList from "~/components/NotificationList.vue";

export default {
    components: {
        HeaderMenu,
    },
};
</script>
