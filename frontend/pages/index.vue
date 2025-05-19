<template>
  <div class="col-span-12">
    <div class="card">
      <div class="font-semibold text-xl mb-4">
        <h1>Новости</h1>

        <InputGroup class="mt-2">
          <InputText
            v-model="search"
            type="search"
            placeholder="Поиск по заголовку или содержимому"
            @keyup.enter="onSearch"
          />
          <Button label="Поиск" @click="onSearch" />
        </InputGroup>

        <div v-if="loading">
          <Card v-for="i in 5" :key="i" class="mt-4">
            <template #title>
              <Skeleton height="28px"></Skeleton>
            </template>
            <template #content>
              <Skeleton height="64px"></Skeleton>
            </template>
          </Card>
        </div>

        <div v-else class="flex flex-col">
          <Card
            v-if="news.length > 0"
            v-for="(item, index) in news"
            :key="item.id"
            class="mt-4"
          >
            <template #title>
              <div
                class="flex items-center justify-between bg-gray-100 px-2 rounded"
              >
                <div class="flex-1">
                  <span class="text-lg font-bold text-gray-800">{{
                    item.title
                  }}</span>
                </div>
                <div class="ml-4 flex flex-col items-end text-xs text-gray-500">
                  <span v-if="!loading">{{ item.source }}</span>
                  <span v-if="!loading">{{
                    new Date(item.createdAt).toLocaleDateString("ru-RU")
                  }}</span>
                </div>
              </div>
            </template>
            <template #content>
              <div class="px-2 py-3 bg-white">
                <span class="text-gray-700 line-clamp-3">{{
                  item.content
                }}</span>
              </div>
            </template>
          </Card>
        </div>

        <Paginator
          v-if="rows < total || !loading"
          :rows="rows"
          :totalRecords="total"
          :first="first"
          :lazy="true"
          @page="onPage"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Paginator from "primevue/paginator";
import Card from "primevue/card";
import Skeleton from "primevue/skeleton";
import InputGroup from "primevue/inputgroup";
import InputText from "primevue/inputtext";
import Button from "primevue/button";

interface NewsItem {
  id: number;
  title: string;
  content: string;
  createdAt: string;
  source: string;
}

interface PageEvent {
  page: number;
  first: number;
  rows: number;
  pageCount?: number;
}

const news = ref<NewsItem[]>([]);
const total = ref<number>(0);
const rows = ref<number>(5);
const first = ref<number>(0);
const search = ref<string>("");
const loading = ref<boolean>(true);

async function loadNews(page: number = 1): Promise<void> {
  loading.value = true;
  try {
    const response = await fetch(
      `http://localhost/api/news?page=${page}&limit=${rows.value}&search=${search.value}`,
      {
        headers: {
          Authorization: `Bearer ${useCookie("jwt").value}`,
        },
      }
    );
    const data = await response.json();
    news.value = data.items;
    total.value = data.total === 0 ? 1 : data.total;
    first.value = (page - 1) * rows.value;
  } finally {
    loading.value = false;
  }
}

function onPage(event: PageEvent): void {
  const page = event.page + 1;
  loadNews(page);
}

function onSearch() {
  loadNews(1); // Always start from the first page when searching
}

export default {
  methods: {
    onPage: onPage,
    loadNews: loadNews,
    onSearch: onSearch,
  },
  data() {
    return {
      news: news,
      rows: rows,
      total: total,
      first: first,
      loading: loading,
      search: search,
    };
  },
  setup() {
    onMounted(() => {
      loadNews();
    });
  },
};
</script>
