<script>
import axios from "axios";
import Header from "./Header.vue";
import Footer from "./Footer.vue";

// Servicio API separado
const quotesApi = {
    getAll: (page) =>
        axios.get("/api/quotes", {
            params: {
                page,
            },
        }),
    getRandom: () => axios.get("/api/quotes/random"),
    getById: (id) => axios.get(`/api/quotes/${id}`),
};

export default {
    components: { Header, Footer },

    data() {
        return {
            quotes: null,
            id: "",
            loading: false,
            error: null,
            pagination: {
                current_page: 1,
                total_pages: 0,
            },
        };
    },

    methods: {
        async handleApiRequest(apiCall) {
            this.loading = true;
            this.error = null;
            this.quotes = null;

            try {
                const { data } = await apiCall(this.pagination.current_page);
                this.quotes = Array.isArray(data.quotes)
                    ? data.quotes
                    : data.quote
                    ? [data]
                    : null;
                this.pagination.total_pages = data.pagination?.total_pages
                    ? data.pagination?.total_pages
                    : 1;
                this.error = this.quotes ? null : "Quote not found";
            } catch (error) {
                console.error("API Error:", error);
                this.error = "Failed to fetch quotes. Please try again.";
                this.quotes = null;
            } finally {
                this.loading = false;
            }
        },

        fetchAll() {
            this.handleApiRequest(quotesApi.getAll);
        },

        fetchRandom() {
            this.handleApiRequest(quotesApi.getRandom);
        },

        fetchById() {
            if (!this.id) {
                this.error = "Please enter a quote ID";
                return;
            }
            this.handleApiRequest(() => quotesApi.getById(this.id));
        },
        nextPage() {
            this.pagination.current_page++;
            this.fetchAll();
        },
        previousPage() {
            this.pagination.current_page--;
            this.fetchAll();
        },
    },
};
</script>

<template>
    <main class="min-h-screen flex flex-col" id="main-app">
        <Header title="Test for get Quotes from API" />

        <div id="content-app" class="flex-grow">
            <div class="container mx-auto p-4">
                <div class="wrap-quotes space-y-6">
                    <!-- Controls Section -->
                    <div class="bg-gray-100 p-4 rounded-lg shadow">
                        <div
                            class="actions flex flex-col md:flex-row gap-4 justify-between"
                        >
                            <div class="buttons flex flex-wrap gap-2">
                                <button
                                    class="button bg-green-500 hover:bg-green-600 disabled:opacity-50"
                                    @click="fetchAll"
                                    :disabled="loading"
                                >
                                    Get All Quotes
                                </button>
                                <button
                                    class="button bg-blue-500 hover:bg-blue-600 disabled:opacity-50"
                                    @click="fetchRandom"
                                    :disabled="loading"
                                >
                                    Get Random Quote
                                </button>
                            </div>

                            <div class="inputs flex flex-1 gap-2 max-w-md">
                                <input
                                    v-model.trim="id"
                                    placeholder="Search Quote By Id"
                                    class="input flex-1"
                                    :disabled="loading"
                                    @keyup.enter="fetchById"
                                />
                                <button
                                    class="button bg-yellow-500 hover:bg-yellow-600 disabled:opacity-50"
                                    @click="fetchById"
                                    :disabled="!id || loading"
                                >
                                    Search
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="text-center p-8">
                        <div
                            class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"
                        ></div>
                        <p class="mt-2 text-gray-600">Loading quotes...</p>
                    </div>

                    <!-- Error Message -->
                    <div
                        v-if="error"
                        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                        role="alert"
                    >
                        <span class="block sm:inline">{{ error }}</span>
                    </div>

                    <!-- Results -->
                    <ul v-if="quotes && quotes.length" class="space-y-4">
                        <li
                            v-for="(item, index) in quotes"
                            :key="index"
                            class="quote-item bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow"
                        >
                            <p class="text-lg italic">"{{ item.quote }}"</p>
                            <span class="block mt-2 text-gray-600 font-medium"
                                >- {{ item.author }} -</span
                            >
                        </li>
                    </ul>

                    <!-- Empty State -->
                    <div
                        v-else-if="quotes && !quotes.length"
                        class="text-center text-gray-500 p-8"
                    >
                        No quotes found
                    </div>

                    <div  v-if="quotes && quotes.length>1" class="pagination flex gap-4 py-3 items-center justify-end">
                        <button class="button bg-gray-400"
                            @click="previousPage"
                            :disabled="pagination.current_page === 1"
                        >
                            Anterior
                        </button>

                        <span class="text-sm font-semibold"
                            >Página {{ pagination.current_page }} de
                            {{ pagination.total_pages }}</span
                        >

                        <button class="button bg-gray-400"
                            @click="nextPage"
                            :disabled="
                                pagination.current_page ===
                                pagination.total_pages
                            "
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Footer />
    </main>
</template>
