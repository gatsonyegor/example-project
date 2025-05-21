<template>
    <Toast />
</template>

<script lang="ts">
import Toast from 'primevue/toast'
import { defineComponent } from 'vue'
import { useNotifications } from '~/composables/useNotifications'
import { useToast } from "primevue/usetoast";

export default defineComponent({
  setup() {
    const toast = useToast();
    const { notifications, removeNotification } = useNotifications();

    watch(
      notifications,
      (currentNotifications) => {
        for (const notification of currentNotifications) {
          toast.add({
            severity: 'info',
            summary: notification.title,
            detail: notification.message,
            life: 3000,
          });

          removeNotification(notification.id);
        }
      },
      { deep: true }
    );

    return {
      notifications,
    };
  },
});

</script>