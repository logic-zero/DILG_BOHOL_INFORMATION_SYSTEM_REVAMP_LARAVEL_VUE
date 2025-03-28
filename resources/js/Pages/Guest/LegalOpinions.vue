<template>
    <div class="p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Legal Opinions
        </h1>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
            <div class="flex flex-col md:flex-row gap-3 w-full mb-4">
                <div class="relative w-full md:w-1/2">
                    <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                    <input 
                    v-model="filters.search" 
                    type="text" 
                    placeholder="Search legal opinions..."
                    class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none" 
                    />
                </div>

                <div class="flex items-center w-full md:w-1/4">
                    <select 
                    id="category" 
                    v-model="filters.category"
                    class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none"
                    >
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category" :value="category">
                        {{ ucFirst(category) }}
                    </option>
                    </select>
                </div>

                <div class="flex items-center w-full md:w-1/4">
                    <select 
                    id="lo_filter" 
                    v-model="filters.loFilter"
                    class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none"
                    >
                    <option value="">All Opinions</option>
                    <option value="lo">LO Opinions</option>
                    </select>
                </div>
            </div>
        </div>

        <div v-if="filteredOpinions.length > 0" class="space-y-4 md:px-12">
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <div v-for="(opinion, index) in filteredOpinions" :key="opinion.id"
                    class="grid grid-cols-12 p-5 border-b border-gray-200 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-all duration-200"
                    @click="toggleOpinion(opinion.id)">
                    <div class="col-span-1 text-sm text-gray-700 flex items-center justify-center">
                        {{ getIncrementedNumber(index) }}
                    </div>

                    <div class="col-span-8 space-y-2 ml-3">
                        <h2 class="text-md font-semibold text-blue-900">{{ opinion.title || 'None' }}</h2>
                        <p class="text-sm text-gray-600">
                            <span class="text-red-600">Category:</span> {{ opinion.category || 'None' }}
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="text-red-600">Reference No:</span> {{ opinion.reference || 'None' }}
                        </p>
                        <a :href="opinion.download_link" target="_blank" download 
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-3-3m3 3l3-3m-9 6h12"></path>
                            </svg>
                            Download Attachment
                        </a>
                    </div>

                    <div class="col-span-3 text-sm text-gray-600 flex items-center justify-end gap-2">
                        <div class="w-24 text-right mr-1">
                            {{ formatDate(opinion.date) || 'None' }}
                        </div>
                        <i :class="{ 'fas fa-chevron-down': selectedOpinionId === opinion.id, 'fas fa-chevron-right': selectedOpinionId !== opinion.id }"
                            class="text-gray-600 text-lg w-6 text-center transition-transform duration-300"
                            :style="{ transform: selectedOpinionId === opinion.id ? 'rotate(180deg)' : 'rotate(0deg)' }"></i>
                    </div>

                    <div :style="{ maxHeight: selectedOpinionId === opinion.id ? '500px' : '0' }"
                        class="col-span-12 overflow-hidden transition-max-height duration-300 ease-out">
                        <div class="mt-4 border-t pt-4">
                            <div v-if="isMobile"
                                class="border border-red-500 bg-red-100 text-red-700 p-4 rounded text-center w-full">
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-lg mr-2"></i>
                                    <p class="text-xs font-semibold">
                                        PDF preview is not supported on mobile. Please use a desktop to view it or download
                                        the file above.
                                    </p>
                                </div>
                            </div>

                            <div v-else class="relative">
                                <div
                                    class="absolute top-2 right-5 bg-white text-xs px-3 py-1 rounded shadow-md">
                                    <i class="fas fa-search-plus mr-1"></i>
                                    Hold <span class="font-bold">Ctrl</span> + <span class="font-bold">Scroll</span> to zoom
                                </div>

                                <!-- PDF.js Container -->
                                <div ref="pdfContainer" class="pdf-container overflow-auto" style="height: 500px;">
                                    <canvas v-for="(page, pageIndex) in pdfPages[opinion.id] || []" :key="pageIndex"
                                        :ref="'pdfCanvas' + opinion.id + '-' + pageIndex"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p v-else class="text-center text-gray-500 mt-4">No legal opinions found.</p>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-gray-700">
            <span>{{ paginationInfo }}</span>
            <div class="flex flex-wrap space-x-2 mt-2 sm:mt-0">
                <button v-for="(link, index) in pagination.links" :key="index" @click="goToPage(link.url)"
                    v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }" class="px-4 py-1 border border-gray-300 hover:bg-gray-200 transition" :disabled="!link.url">
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import * as pdfjsLib from "pdfjs-dist"; 
pdfjsLib.GlobalWorkerOptions.workerSrc = "/js/pdf.worker.min.js";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const opinions = ref(pageProps.pagination.data ?? []);
const pagination = ref(pageProps.pagination);
const categories = ref(pageProps.categories?.filter(category => category) ?? []);
const loadingPdf = ref(null);
const pdfError = ref(null);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    category: pageProps.filters?.category ?? "",
    loFilter: pageProps.filters?.loFilter ?? "",
});

const selectedOpinionId = ref(null);
const isMobile = computed(() => window.innerWidth <= 768);
const pdfPages = ref({}); 

const renderPdf = async (opinionId, pdfUrl) => {
    loadingPdf.value = opinionId;
    pdfError.value = null;
    
    try {
        const proxyUrl = `/api/proxy-pdf?url=${encodeURIComponent(pdfUrl)}`;
        const loadingTask = pdfjsLib.getDocument(proxyUrl);
        const pdf = await loadingTask.promise;

        const container = document.querySelector(`[data-pdf-container="${opinionId}"]`);
        container.innerHTML = '';

        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const viewport = page.getViewport({ scale: 1.5 });
            
            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-page';
            const context = canvas.getContext('2d');
            
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;
            
            container.appendChild(canvas);
        }
    } catch (error) {
        console.error('Error rendering PDF:', error);
        pdfError.value = opinionId;
    } finally {
        loadingPdf.value = null;
    }
};

watch(selectedOpinionId, async (newOpinionId) => {
    if (newOpinionId) {
        const opinion = filteredOpinions.value.find(op => op.id === newOpinionId);
        if (opinion && opinion.download_link) {
            await renderPdf(newOpinionId, opinion.download_link);
        }
    }
});

const applyFilters = () => {
    router.get("/legalOpinions", filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["pagination", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.pagination;
            opinions.value = props.pagination.data;
        },
    });
};

const debouncedSearch = debounce(applyFilters, 500);

watch(() => filters.value.search, debouncedSearch);
watch(() => filters.value.category, applyFilters);
watch(() => filters.value.loFilter, applyFilters);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to ? `Showing ${from} to ${to} of ${total} entries` : "No results found";
});

const goToPage = (url) => {
    if (!url) return;
    router.get(url, filters.value, { 
        preserveState: true,
        preserveScroll: true,
        only: ["pagination"],
        onSuccess: ({ props }) => {
            pagination.value = props.pagination;
            opinions.value = props.pagination.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const toggleOpinion = (opinionId) => {
    selectedOpinionId.value = selectedOpinionId.value === opinionId ? null : opinionId;
};

const ucFirst = (str) => {
    if (!str) return "";
    return str.charAt(0).toUpperCase() + str.slice(1);
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
};

const getIncrementedNumber = (index) => {
    const currentPage = pagination.value.current_page || 1;
    const perPage = pagination.value.per_page || 10;
    return (currentPage - 1) * perPage + index + 1;
};

const filteredOpinions = computed(() => {
    let filtered = opinions.value;

    if (filters.value.loFilter === "lo") {
        filtered = filtered.filter(opinion => {
            return opinion.title.toLowerCase().startsWith("lo");
        });
    }

    return filtered;
});
</script>

<style scoped>
.transition-max-height {
    transition-property: max-height;
    transition-timing-function: ease-out;
}

.pdf-page {
    display: block;
    margin: 0 auto 10px;
    border: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pdf-container {
    border: 1px solid #ccc;
    background-color: #f9f9f9;
}

.pdf-container canvas {
    display: block;
    margin-bottom: 10px;
    border-bottom: 1px solid #ddd;
}
</style>