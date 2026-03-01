<script setup lang="ts">
import type { Artiste } from '~/types/Artiste'

const route = useRoute()
const { data: artist, error } = await useAsyncData<Artiste>(`artist-${route.params.id}`, () => api.get(`/api/artistes/${route.params.id}`, {
    params: {
        tracks_limit: 500 // Fetch more tracks for the artist page
    }
}))

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
</script>

<template>
  <div class="min-h-screen bg-ui-bg text-gray-900 dark:text-white pb-20 md:pb-24">
    <ArtistHeader :artist="artist" />

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
          <TrackList title="Popular" :tracks="artist?.popularTracks" />
        </div>
      </div>
    </div>
  </div>
</template>
