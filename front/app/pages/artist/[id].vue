<script setup lang="ts">
import type { Artiste } from '~/types/Artiste'

const route = useRoute()
const { data: artist, error } = await useAsyncData<Artiste>(`artist-${route.params.id}`, () => api.get(`/api/artistes/${route.params.id}`))

if (error.value) {
    console.error('AsyncData Error:', error.value)
    throw createError({
        statusCode: error.value.statusCode || 500,
        statusMessage: error.value.message || 'Error fetching artist',
        data: error.value
    })
}

if (!artist.value) {
    console.error('Artist not found (null value)')
    throw createError({ statusCode: 404, statusMessage: 'Artist not found' })
}

// Extract dominant color from artist image
const { extractDominantColor } = useDominantColor()
const dominantColor = ref('rgb(82, 82, 91)') // default gray

onMounted(async () => {
    const imageUrl = prefixApiResource(artist.value?.imageUrl || '')
    if (imageUrl) {
        dominantColor.value = await extractDominantColor(imageUrl)
    }
})
</script>

<template>
  <div class="min-h-screen bg-ui-bg text-gray-900 dark:text-white pb-20 md:pb-24">
    <!-- Artist Banner -->
    <div class="relative h-[30vh] md:h-[40vh] min-h-[250px] md:min-h-[340px] w-full bg-gray-200 dark:bg-neutral-800">
      <!-- Background Image -->
      <img :src="prefixApiResource(artist?.imageUrl)" class="absolute inset-0 bg-cover bg-center object-cover bg-no-repeat w-full h-full" :ui="{ rounded: 'rounded-none' }" />

      <!-- Gradient Overlay -->
      <div 
        class="absolute inset-0 transition-colors duration-500"
        :style="{ background: `linear-gradient(to top, var(--ui-bg), ${dominantColor}00 50%, transparent)` }"
      ></div>

      <div class="absolute bottom-0 left-0 p-4 md:p-8 w-full z-10 flex flex-col gap-2 md:gap-4">
        <div v-if="artist?.verified" class="flex items-center gap-2 text-gray-900/90 dark:text-white/90">
          <UIcon name="i-lucide-badge-check" class="text-blue-400 w-5 h-5 md:w-6 md:h-6 fill-current" />
          <span class="text-xs md:text-sm font-medium">Verified Artist</span>
        </div>
        <h1 class="text-3xl md:text-7xl lg:text-9xl font-extrabold tracking-tight drop-shadow-lg mb-1 md:mb-2 text-white break-words">
          {{ artist?.nom }}
        </h1>
        <p class="text-sm md:text-base lg:text-lg font-medium drop-shadow-md text-gray-100 dark:text-white/90">
          {{ artist?.listeners }}
        </p>
      </div>
    </div>

    <!-- Content -->
    <div class="px-4 md:px-6 py-4 md:py-6 bg-linear-to-b from-ui-bg/50 to-ui-bg">
      <!-- Controls -->
      <div class="flex items-center gap-3 md:gap-6 mb-6 md:mb-8">
        <UButton
          icon="i-lucide-play"
          color="primary"
          variant="solid"
          class="rounded-full w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-green-500 hover:bg-green-400 text-black hover:scale-105 transition-transform shadow-lg"
          :ui="{ rounded: 'rounded-full', icon: { size: { xl: 'w-6 h-6 md:w-8 md:h-8' } } }"
          size="xl" />
        <UButton
          label="Follow"
          variant="outline"
          color="neutral"
          class="px-4 md:px-6 py-1.5 rounded-full font-bold border-gray-400 dark:border-neutral-500 hover:border-black dark:hover:border-white uppercase text-xs tracking-widest text-gray-900 dark:text-white" />
        <UButton icon="i-lucide-ellipsis" color="neutral" variant="ghost" class="text-gray-500 dark:text-neutral-400 hover:text-black dark:hover:text-white" size="xl" />
      </div>

      <div class="flex flex-col lg:flex-row gap-12">
        <!-- Popular Tracks (Left Column) -->
        <div class="flex-1">
          <TrackList title="Popular" :tracks="artist?.popularTracks" see-more-text="See more" />
        </div>

        <!-- Artist Pick / Right Column Placeholder (Optional) -->
        <div class="w-full lg:w-[30%] hidden lg:block" v-if="artist.popularTracks.length > 0">
          <h2 class="text-2xl font-bold mb-4">Artist Pick</h2>
          <div class="bg-gray-100 dark:bg-neutral-800/50 p-4 rounded-md flex gap-4 items-start hover:bg-gray-200 dark:hover:bg-neutral-800 transition cursor-pointer">
            <img :src="prefixApiResource(artist.popularTracks[0].album?.coverUrl || artist.imageUrl)" class="w-20 h-20 rounded object-cover shadow-lg" />
            <div>
              <div class="flex items-center gap-2 mb-1">
                <UAvatar :src="prefixApiResource(artist?.imageUrl)" size="3xs" />
                <span class="text-xs text-gray-500 dark:text-neutral-400 text-sm">
                  Posted By
                  {{ artist.nom }}
                </span>
              </div>
              <p class="font-bold hover:underline mb-1">{{ artist.popularTracks[0].titre }}</p>
              <p class="text-sm text-gray-500 dark:text-neutral-400">Track</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Discography -->
      <div class="mt-12">
        <h2 class="text-2xl font-bold mb-4">Discography</h2>
        <UTabs
          :items="[{ label: 'Popular releases', slot: 'popular' }, { label: 'Albums', slot: 'albums' }, { label: 'Singles and EPs', slot: 'singles' }]"
          class="w-full mb-6"
          :ui="{ list: { background: 'bg-transparent', marker: { background: 'bg-transparent' }, tab: { active: 'text-gray-900 dark:text-white bg-gray-200 dark:bg-neutral-800', inactive: 'text-gray-500 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-white', height: 'h-8', size: 'text-sm', padding: 'px-4' } } }"></UTabs>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
          <MusicCard v-for="album in artist.albums" :key="album.id" :to="`/album/${album.id}`" v-bind="album" />
        </div>
      </div>
    </div>
  </div>
</template>
