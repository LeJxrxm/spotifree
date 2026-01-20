<script setup lang="ts">
import type { Artiste } from '~/types/Artiste'
import type { Album } from '~/types/Album'

const shortcuts = [
  {
    label: 'Liked Songs',
    image: 'https://images.unsplash.com/photo-1493225255756-d9584f8606e9?q=80&w=150&auto=format&fit=crop',
    icon: 'i-lucide-heart'
  },
  {
    label: 'Discover Weekly',
    image: 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=150&auto=format&fit=crop'
  },
  {
    label: 'On Repeat',
    image: 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?q=80&w=150&auto=format&fit=crop'
  },
  {
    label: 'Daily Mix 1',
    image: 'https://images.unsplash.com/photo-1514525253440-b39345208668?q=80&w=150&auto=format&fit=crop'
  },
  {
    label: 'Release Radar',
    image: 'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?q=80&w=150&auto=format&fit=crop'
  },
  {
    label: 'Top Hits',
    image: 'https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=150&auto=format&fit=crop'
  }
]

const { data: artists } = await useAsyncData<Artiste[]>('artists', () => api.get('/api/artistes'))
const { data: albums } = await useAsyncData<Album[]>('albums', () => api.get('/api/albums'))

const sections = computed(() => {
  const list = []

  if (artists.value && artists.value.length > 0) {
    list.push({
      title: 'Artists',
      items: artists.value.map((a: Artiste) => ({
        title: a.nom,
        description: 'Artist',
        imageUrl: a.imageUrl ?? 'https://placehold.co/400',
        to: `/artist/${a.id}`
      }))
    })
  }

  if (albums.value && albums.value.length > 0) {
    list.push({
      title: 'All Albums',
      items: albums.value.map((album: Album) => ({
        title: album.titre,
        description: album.artiste?.nom || 'Unknown Artist',
        coverUrl: album.coverUrl ?? (album.artiste?.imageUrl ?? 'https://placehold.co/400'),
        to: `/album/${album.id}`
      }))
    })
  }

  return list
})
</script>

<template>
  <div class="p-6 pb-24 md:pb-8 space-y-8 min-h-screen text-gray-900 dark:text-white">
    <!-- Sections -->
    <section v-for="section in sections" :key="section.title">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold hover:underline cursor-pointer">{{ section.title }}</h2>
        <UButton variant="link" color="neutral"
          class="text-sm font-bold uppercase tracking-wider hover:underline text-neutral-400">Show all</UButton>
      </div>

      <UCarousel
        v-slot="{ item }"
        :items="section.items"
        :ui="{
          item: 'basis-1/2 sm:basis-1/3 md:basis-1/4 lg:basis-1/5 xl:basis-1/6',
          container: 'gap-6'
        }"
      >
        <MusicCard v-bind="item" />
      </UCarousel>
    </section>
  </div>
</template>
