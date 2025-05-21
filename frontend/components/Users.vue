<template>
    <div class="col-span-12">
        <h1 class="text-3xl font-bold mb-6">Пользователи</h1>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4"
                        >
                            Логин
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/4"
                        >
                            Статус
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4"
                        >
                            Последний код верификации
                        </th>
                    </tr>
                </thead>

                <tbody v-if="loading" class="bg-white divide-y divide-gray-200">
                    <tr v-for="i in 5" :key="i">
                        <td
                            colspan="4"
                            class="px-6 py-4 text-center text-gray-500"
                        >
                            <Skeleton
                                type="text"
                                :style="{ width: '100%', height: '20px' }"
                            />
                        </td>
                    </tr>
                </tbody>

                <tbody v-else class="bg-white divide-y divide-gray-200">
                    <tr
                        v-if="users.length > 0"
                        v-for="user in users"
                        :key="user.id"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ user.login }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="[
                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                    user.isVerified
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{
                                    user.isVerified
                                        ? "Зарегистрирован"
                                        : "Ожидает"
                                }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ user.lastVerificationCode || "N/A" }}
                        </td>
                    </tr>

                    <tr v-else>
                        <td
                            colspan="4"
                            class="px-6 py-4 text-center text-gray-500"
                        >
                            Пользователи не найдены
                        </td>
                    </tr>
                </tbody>
            </table>

            <Paginator
                v-if="rows < total && !loading"
                :rows="rows"
                :totalRecords="total"
                :first="first"
                :lazy="true"
                @page="onPage"
            />
        </div>
    </div>
</template>

<script lang="ts">
import { ref, defineComponent, onMounted } from "vue";
import { useCookie, useRuntimeConfig } from "#app";

interface UserItem {
    id: number;
    login: string;
    isVerified: boolean;
    lastVerificationCode: string;
}

interface PageEvent {
    page: number;
    first: number;
    rows: number;
    pageCount?: number;
}

const users = ref<UserItem[]>([]);
const total = ref<number>(0);
const rows = ref<number>(5);
const first = ref<number>(0);
const loading = ref<boolean>(true);

async function loadUsers(page: number = 1): Promise<void> {
    try {
        const token = useCookie("jwt");
        const config = useRuntimeConfig();
        const response = await fetch(
            `${config.public.apiBase}/api/user/list?page=${page}&limit=${rows.value}`,
            {
                headers: {
                    Authorization: `Bearer ${token.value}`,
                },
            }
        );

        const data = await response.json();
        if (data.error) {
            console.error("Error fetching users:", data.message);
            return;
        }

        loading.value = false;
        users.value = data.items;
        total.value = data.total;
    } catch (error) {
        console.error("Error fetching users:", error);
    }
}

function onPage(event: PageEvent): void {
    const page = event.page + 1;
    loadUsers(page);
}

// Fetch users on component mount
export default defineComponent({
    data() {
        return {
            users: users,
            loading: loading,
            rows: rows,
            total: total,
            first: first,
        };
    },
    methods: {
        onPage: onPage,
        loadUsers: loadUsers,
    },
    setup() {
        onMounted(async () => {
            loadUsers();
        });
    },
});
</script>
