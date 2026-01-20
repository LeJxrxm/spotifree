<script setup lang="ts">
import type { Album } from '~/types/Album'

const route = useRoute()
const { data: album, error } = await useAsyncData<Album>(`album-${route.params.id}`, () => api.get(`/api/albums/${route.params.id}`))

if (error.value) {
    console.error('AsyncData Error:', error.value)
    throw createError({
        statusCode: error.value.statusCode || 500,
        statusMessage: error.value.message || 'Error fetching album',
        data: error.value
    })
}

if (!album.value) {
    console.error('Album not found (null value)')
    throw createError({ statusCode: 404, statusMessage: 'Album not found' })
}

const tracks = computed(() => album.value?.musiques || [])

const totalDuration = computed(() => {
    const total = tracks.value.reduce((acc, track) => acc + (track.duree || 0), 0)
    const hours = Math.floor(total / 3600)
    const minutes = Math.floor((total % 3600) / 60)
    
    if (hours > 0) {
        return `about ${hours} hr ${minutes} min`
    }
    return `${minutes} min`
})

const albumYear = computed(() => {
    return album.value?.createdAt ? new Date(album.value.createdAt).getFullYear() : new Date().getFullYear()
})

// Extract dominant color from cover
const { extractDominantColor } = useDominantColor()
const dominantColor = ref('primary') // default red

onMounted(async () => {
    const imageUrl = prefixApiResource(album.value?.coverUrl || album.value?.artiste?.imageUrl || '')
    if (imageUrl) {
        dominantColor.value = await extractDominantColor(imageUrl)
    }
})
</script>

<template>
    <div class="min-h-screen bg-ui-bg text-gray-900 dark:text-white pb-20 md:pb-24">
        <!-- Album Header with Gradient -->
        <div
            class="pt-12 md:pt-20 pb-4 md:pb-6 px-4 md:px-6 flex flex-col md:flex-row items-center md:items-end gap-4 md:gap-6 transition-colors duration-500"
            :style="{ background: `linear-gradient(to bottom, ${dominantColor}, var(--ui-bg))` }">
            <img :src="prefixApiResource(album.coverUrl || album.artiste?.imageUrl || 'https://placehold.co/400')" :alt="album.titre"
                class="w-40 h-40 md:w-52 md:h-52 shadow-2xl shadow-black/50 aspect-square object-cover rounded-md shrink-0" />

            <div class="flex flex-col gap-2 text-center md:text-left w-full">
                <span class="text-xs font-bold uppercase tracking-wider text-white truncate">Album</span>
                <h1 class="text-2xl md:text-5xl lg:text-7xl font-bold text-white tracking-tight mb-2 md:mb-4 drop-shadow-md break-words">{{ album.titre }}</h1>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 text-xs md:text-sm font-medium text-white/90">
                    <NuxtLink v-if="album.artiste" :to="`/artist/${album.artiste.id}`" class="hover:underline cursor-pointer font-bold flex items-center gap-2">
                        <UAvatar :src="prefixApiResource(album.artiste.imageUrl)" size="2xs" />
                        {{ album.artiste.nom }}
                    </NuxtLink>
                    <span class="hidden md:inline">•</span>
                    <span>{{ albumYear }}</span>
                    <span class="hidden md:inline">•</span>
                    <span>{{ tracks.length }} songs</span>
                    <span class="hidden sm:inline">•</span>
                    <span class="text-gray-300 hidden sm:inline">{{ totalDuration }}</span>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-4 md:px-6 relative z-10">
            <!-- Toolbar -->
            <div class="flex items-center justify-between mb-6 md:mb-8">
                <div class="flex items-center gap-3 md:gap-6">
                    <UButton icon="i-lucide-play" color="primary" variant="solid"
                        class="rounded-full w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-green-500 hover:bg-green-400 text-black hover:scale-105 transition-transform shadow-lg"
                        :ui="{ rounded: 'rounded-full', icon: { size: { xl: 'w-6 h-6 md:w-8 md:h-8' } } }" size="xl" />
                    <UButton icon="i-lucide-shuffle" color="neutral" variant="ghost"
                        class="text-gray-500 dark:text-neutral-400 hover:text-black dark:hover:text-white hidden md:inline-flex" size="xl" />
                    <UButton icon="i-lucide-circle-check" color="neutral" variant="ghost" class="text-green-500 hidden sm:inline-flex"
                        size="xl" />
                    <UButton icon="i-lucide-arrow-down-circle" color="neutral" variant="ghost"
                        class="text-gray-500 dark:text-neutral-400 hover:text-black dark:hover:text-white hidden md:inline-flex" size="xl" />
                    <UButton icon="i-lucide-ellipsis" color="neutral" variant="ghost"
                        class="text-gray-500 dark:text-neutral-400 hover:text-black dark:hover:text-white" size="xl" />
                </div>

                <div class="flex items-center gap-2 hidden md:flex">
                    <UButton label="List" icon="i-lucide-list" variant="ghost" color="neutral"
                        class="text-gray-500 dark:text-neutral-400 hover:text-black dark:hover:text-white" />
                </div>
            </div>

            <!-- Tracklist Header (desktop only) -->
            <div
                class="hidden md:grid grid-cols-[auto_1fr_auto_auto] gap-4 px-4 py-2 border-b border-gray-200 dark:border-neutral-800 text-gray-500 dark:text-neutral-400 text-sm font-medium uppercase tracking-wider mb-4 sticky top-16 backdrop-blur-sm z-20">
                <div class="w-8 text-center">#</div>
                <div>Title</div>
                <div class="text-right">Plays</div>
                <div class="w-16 text-right">
                    <UIcon name="i-lucide-clock" class="w-4 h-4 inline" />
                </div>
            </div>

            <!-- Disc Divider -->
            <div class="flex items-center gap-4 my-4 px-4 text-gray-500 dark:text-neutral-400 text-sm font-bold">
                <UIcon name="i-lucide-disc-3" />
                <span>Disc 1</span>
            </div>

            <!-- Tracks -->
            <TrackList :tracks="tracks" />

            <div class="mt-8 text-sm text-gray-500 dark:text-neutral-400 px-4">
                <p class="mb-2">{{ new Date(album.createdAt || Date.now()).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                <p class="text-xs">℗ {{ albumYear }} {{ album.artiste?.nom }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Optional: Custom scrollbar or specific adjustments */
</style>
