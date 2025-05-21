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

        if (!jwt.value) {
            setTimeout(connectToSSE, 5000);
            return;
        }

        const config = useRuntimeConfig();
        eventSource = new EventSource(
            `${config.public.apiBase}/api/notifications/stream?jwt=${jwt.value}`
        );

        eventSource.onmessage = (event) => {
            try {
                const eventdata = JSON.parse(event.data);
                if (eventdata.length > 0) {
                    if (event.type === "message") {
                        const messagedata = JSON.parse(eventdata);
                        addNotification({
                            id: crypto.randomUUID(),
                            ...messagedata,
                            title: messagedata.title || "Уведомление",
                            message: messagedata.message,
                            isShown: false,
                        });
                    }
                }
            } catch (error) {
                console.error("Ошибка при разборе уведомления:", error);
            }
        };

        eventSource.onerror = (error) => {
            if (eventSource?.readyState === EventSource.CLOSED) {
                eventSource.close();
                // Attempt to reconnect after 5 seconds
                setTimeout(connectToSSE, 5000);
            }
        };
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
