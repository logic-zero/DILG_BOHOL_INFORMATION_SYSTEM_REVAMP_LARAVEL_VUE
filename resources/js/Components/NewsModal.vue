<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
        @click.self="closeModal">
        <div
            class="bg-white shadow-lg w-full sm:w-[70vw] md:w-[60vw] lg:w-[50vw] xl:w-[45vw] h-[95vh] max-h-[95vh] flex flex-col">
            <div class="px-4 py-3 border-b flex justify-between items-center">
                <div class="flex flex-wrap items-center">
                    <p class="text-sm uppercase text-gray-700 font-black flex-shrink-0 mr-2">
                        {{ formattedDate }}
                    </p>
                    <div>
                        <Link :href="`/guestNews/${newsItem.id}`" class="text-blue-500 text-xs uppercase font-bold underline">
                            View Full Page
                        </Link>
                        <button
                            @click="shareOnFacebook(newsItem.id)"
                            class="flex-shrink-0 text-xs font-bold ml-2 items-center hover:underline text-white bg-blue-500 py-1 px-2 rounded transition"
                        >
                            <i class="fab fa-facebook-f mr-2"></i>
                            Share
                        </button>
                    </div>
                </div>

                <button @click="closeModal" class="text-gray-500 font-bold hover:text-gray-700">
                    <i class="fa-solid fa-xmark text-lg cursor-pointer"></i>
                </button>
            </div>

            <div class="p-4 overflow-y-auto flex-grow">
                <h5 class="text-xl text-blue-900 font-black mb-5">{{ newsItem.title }}</h5>
                <p class="text-gray-500 text-md whitespace-pre-line">{{ newsItem.caption }}</p>
                <div v-if="newsItem.images.length" class="mt-4 space-y-4">
                    <img v-for="(image, index) in newsItem.images" :key="index" :src="`/storage/${image}`"
                        class="p-1 bg-white shadow-lg shadow-black/50 w-full h-auto object-cover" alt="News Image">
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, defineProps, defineEmits } from "vue";
import { Link } from '@inertiajs/vue3'

const shareOnFacebook = (newsId) => {
    const baseUrl = window.location.origin;
    const shareUrl = `${baseUrl}/guestNews/${newsId}`;
    console.log('Sharing URL:', shareUrl); // Debug line
    const encodedUrl = encodeURIComponent(shareUrl);
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`, '_blank', 'width=600,height=400');
}

const props = defineProps({
    newsItem: Object,
    isOpen: Boolean,
});

const emit = defineEmits(["close"]);

const formattedDate = computed(() => {
    if (!props.newsItem?.created_at) return "";

    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: '2-digit',
        weekday: 'long',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    }).format(new Date(props.newsItem.created_at));
});


const closeModal = () => {
    emit("close");
};
</script>

<style scoped>
button {
    transition: all 0.2s ease-in-out;
}
</style>
