<template>
    <Card class="max-w-md mx-auto p-4">
        <template #title>Вход в систему</template>
        <template #content>
            <div v-if="step === 1">
                <label for="username">Логин</label>
                <InputText id="username" v-model="username" class="w-full mb-6" />
                <Button label="Далее" @click="handleLogin" class="w-full" />
            </div>

            <div v-else-if="step === 2">
                <label for="code">Код из Telegram</label>
                <InputText id="code" v-model="code" maxlength="6" class="w-full mb-4" />
                <Button label="Подтвердить" @click="verifyCode" class="w-full" />
            </div>

            <div v-if="error" class="mt-3 text-red-500">{{ error }}</div>
        </template>
    </Card>
</template>

<script setup>
import { ref } from 'vue'

import Card from 'primevue/card';
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

const step = ref(1)
const username = ref('')
const code = ref('')
const error = ref('')

const handleLogin = async () => {
    if (!username.value) {
        error.value = 'Логин не может быть пустым'
        return
    }

    error.value = ''
    try {
        const response = await fetch('http://localhost/api/auth/request-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ username: username.value })
        })
        const data = await response.json()
        if (data.success) {
            step.value = 2
        } else {
            error.value = data.message || 'Ошибка при запросе кода'
        }
    } catch (e) {
        error.value = 'Ошибка сервера'
    }
}

const verifyCode = async () => {
    error.value = ''
    try {
        const response = await fetch('http://localhost/api/auth/verify-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: username.value,
                code: code.value
            })
        })
        const data = await response.json()

        console.log(data)
        
        if (data.token) {
            useCookie('jwt').value = data.token
            window.location.href = '/' // Consider using Nuxt navigation
        } else {
            error.value = data.message || 'Неверный код'
        }
    } catch (e) {
        error.value = 'Ошибка при проверке кода'
    }
}
</script>
