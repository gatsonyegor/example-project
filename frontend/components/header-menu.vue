<template>

<Menubar :model="items">
    <template #end>
    <div v-ripple class="flex align-items-center">
        <div class="custom-menubar-item-content" @click="logout">
            <span class="pi pi-sign-out"></span>
            <span class="ml-2">Выйти</span>
        </div>
        <!-- <span v-if="item.shortcut" class="ml-auto border-1 surface-border border-round surface-100 text-xs p-1">{{ item.shortcut }}</span> -->
    </div>
    </template>
</Menubar>

</template>

<script lang="ts">
import Menubar from "primevue/menubar";
import { ref } from "vue";

const items = ref([
  {
    label: "Новости",
    icon: "pi pi-folder",
    url: "/",
  },
  {
    label: "Пользователи",
    icon: "pi pi-users",
    url: "/users",
  },
  {
    label: "Связаться",
    icon: "pi pi-envelope",
    url: "https://rostov.hh.ru/resume/133a069cff044b86780039ed1f554733686d53",
    target: "_blank",
  },
]);

const logout = () => {
  useCookie("jwt").value = null;
  navigateTo("/")
};

export default {
  data() {
    return {
      items: items,
    };
  },
  methods: {
    logout,
  },
};
</script>

<style scoped>
.custom-menubar-item-content {
    padding: 0.75rem 1rem;
    cursor: pointer;
    color: var(--p-menubar-item-color);
    user-select: none;
}

.custom-menubar-item-content:hover {
    color: var(--p-menubar-item-focus-color);
    background-color: var(--p-menubar-item-focus-background);
    border-radius: 6px;
    transition: background var(--p-menubar-transition-duration), color var(--p-menubar-transition-duration);
}
</style>