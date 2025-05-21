import { ref, onMounted, onUnmounted } from "vue";
import { useCookie } from "#app";

interface Notification {
  id: string;
  type: string;
  title: string;
  message: string;
}

export const useNotifications = () => {
  const notifications = ref<Notification[]>([]);
  let eventSource: EventSource | null = null;

  const addNotification = (notification: Notification) => {
    notifications.value.unshift(notification);

    if (notifications.value.length > 5) {
      notifications.value = notifications.value.slice(0, 5);
    }
  };

  const removeNotification = (id: string) => {
    notifications.value = notifications.value.filter((n) => n.id !== id);
  };

  const connectToSSE = () => {
    if (eventSource) {
      eventSource.close();
    }

    const jwt = useCookie("jwt");
    eventSource = new EventSource(
      `http://localhost/api/notifications/stream?jwt=${jwt.value}`
    );

    eventSource.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        if (data.length > 0) {
          addNotification({
            id: crypto.randomUUID(),
            ...data[0],
            title: data[0].title || "Уведомление",
            message: data[0].message,
            isShown: false,
          });
        }
      } catch (error) {
        console.error("Ошибка при разборе уведомления:", error);
      }
    };

    eventSource.onopen = () => {
      console.log("Соединение открыто");
    };

    eventSource.onerror = (error) => {
      console.debug("Ошибка соединения:", error);
      if (eventSource?.readyState === EventSource.CLOSED) {
        eventSource.close();
        // Attempt to reconnect after 5 seconds
        setTimeout(connectToSSE, 5000);
      }
    };

    // Handle ping events to keep connection alive
    eventSource.addEventListener("ping", (event) => {
      console.debug("Получен пинг", JSON.parse(event.data));
    });
  };

  onMounted(() => {
    connectToSSE();
  });

  onUnmounted(() => {
    if (eventSource) {
      eventSource.close();
      eventSource = null;
    }
  });

  return {
    notifications,
    addNotification,
    removeNotification,
  };
};
