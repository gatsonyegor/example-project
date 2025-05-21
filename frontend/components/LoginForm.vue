<template>
    <Card class="max-w-md mx-auto p-4">
        <template #title>Вход в систему</template>
        <template #content>
            <div v-if="step === 1">
                <label for="username">Логин</label>
                <InputText
                    id="username"
                    v-model="username"
                    class="w-full mb-6"
                />
                <Button label="Далее" @click="handleLogin" class="w-full" />
            </div>

            <div v-else-if="step === 2">
                <label for="code">Код из Telegram</label>
                <InputMask
                    id="code"
                    v-model="code"
                    mask="999999"
                    placeholder="______"
                    class="w-full mb-4"
                />
                <Button
                    label="Подтвердить"
                    @click="verifyCode"
                    class="w-full"
                />
            </div>

            <div v-if="error" class="mt-3 text-red-500">{{ error }}</div>
        </template>
    </Card>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useRuntimeConfig, navigateTo, useCookie } from "#app";
import Card from "primevue/card";
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import InputMask from "primevue/inputmask";

const step = ref(1);
const username = ref("");
const code = ref("");
const error = ref("");

const validateUsername = (value: string): string => {
    if (!value) {
        return "Логин не может быть пустым";
    }
    if (value.length < 5) {
        return "Логин должен содержать минимум 5 символов";
    }
    if (value.length > 32) {
        return "Логин должен содержать максимум 32 символа";
    }
    if (!/^[a-zA-Z0-9_]+$/.test(value)) {
        return "Логин может содержать только латинские буквы (a-z, A-Z), цифры (0-9) и знаки подчеркивания (_)";
    }
    return "";
};

const validateCode = (value: string): string => {
    if (!value) {
        return "Код не может быть пустым";
    }
    if (value.length !== 6) {
        return "Код должен содержать 6 символов";
    }
    return "";
};

watch(username, (newValue) => {
    if (!newValue) {
        error.value = "";
    } else {
        error.value = validateUsername(newValue);
    }
});

watch(code, (newValue) => {
    if (!newValue) {
        error.value = "";
    } else {
        error.value = validateCode(newValue);
    }
});

const handleLogin = async () => {
    const config = useRuntimeConfig();

    const usernameValidationError = validateUsername(username.value);
    if (usernameValidationError) {
        error.value = usernameValidationError;
        return;
    }

    error.value = "";
    try {
        const response = await fetch(
            `${config.public.apiBase}/api/auth/request-code`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ username: username.value }),
            }
        );
        const data = await response.json();
        if (data.success) {
            step.value = 2;
        } else {
            error.value = data.message || "Ошибка при запросе кода";
        }
    } catch (e) {
        error.value = "Ошибка сервера";
    }
};

const verifyCode = async () => {
    const config = useRuntimeConfig();

    error.value = "";
    try {
        const response = await fetch(
            `${config.public.apiBase}/api/auth/verify-code`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    username: username.value,
                    code: code.value,
                }),
            }
        );
        const data = await response.json();

        if (data.token) {
            const jwt = useCookie("jwt", { maxAge: 3600 });
            jwt.value = data.token;
            navigateTo("/news");
        } else {
            error.value = data.message || "Неверный код";
        }
    } catch (e) {
        error.value = "Ошибка при проверке кода";
    }
};
</script>
