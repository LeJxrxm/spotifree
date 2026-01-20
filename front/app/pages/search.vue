<script setup lang="ts">
import type { Artiste } from '~/types/Artiste'
import type { Album } from '~/types/Album'
import type { Musique } from '~/types/Musique'

const route = useRoute()
const router = useRouter()

const searchQuery = ref((route.query.q as string) || '')
const isLoading = ref(false)

const searchResults = ref<{
  artists: Artiste[]
  albums: Album[]
  tracks: Musique[]
}>({
  artists: [],
  albums: [],
  tracks: []
})

const hasResults = computed(() => {
  return searchResults.value.artists.length > 0 ||
    searchResults.value.albums.length > 0 ||
    searchResults.value.tracks.length > 0
})

let debounceTimer: NodeJS.Timeout | null = null

const performSearch = async (query: string) => {
  if (query.length < 2) {
    searchResults.value = { artists: [], albums: [], tracks: [] }
    return
  }

  isLoading.value = true

  try {
    const [artists, albums, tracks] = await Promise.all([
      api.get<Artiste[]>(`/api/artistes?search=${encodeURIComponent(query)}`).catch(() => []),
      api.get<Album[]>(`/api/albums?search=${encodeURIComponent(query)}`).catch(() => []),
      api.get<Musique[]>(`/api/musiques?search=${encodeURIComponent(query)}`).catch(() => [])
    ])

    searchResults.value = {
      artists: artists || [],
      albums: albums || [],
      tracks: tracks || []
    }
  } catch (e) {
    console.error('Search failed', e)
  } finally {
    isLoading.value = false
  }
}

watch(searchQuery, (newQuery) => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }

  if (newQuery.length < 2) {
    searchResults.value = { artists: [], albums: [], tracks: [] }
    return
  }

  debounceTimer = setTimeout(() => {
    performSearch(newQuery)
  }, 300)
})

// Trigger initial search if query param exists
onMounted(() => {
  if (searchQuery.value && searchQuery.value.length >= 2) {
    performSearch(searchQuery.value)
  }
})

const playerStore = usePlayerStore()

function playTrack(track: Musique) {
  playerStore.play(track)
}
</script>

<template>
  <div class="p-6 pb-24 md:pb-8 space-y-8 min-h-screen text-gray-900 dark:text-white">
    <div class="max-w-4xl">
      <UInput
        v-model="searchQuery"
        icon="i-lucide-search"
        placeholder="Search for artists, albums, or tracks..."
        size="xl"
        autofocus
        class="w-full"
      />
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <UIcon name="i-lucide-loader-2" class="w-8 h-8 animate-spin text-neutral-400" />
    </div>

    <!-- No Results -->
    <div v-else-if="searchQuery.length >= 2 && !hasResults" class="py-12 text-center">
      <UIcon name="i-lucide-search-x" class="w-16 h-16 mx-auto mb-4 text-neutral-400" />
      <h3 class="text-xl font-semibold mb-2">No results found</h3>
      <p class="text-neutral-400">Try searching with different keywords</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="searchQuery.length < 2" class="py-12 text-center">
      <UIcon name="i-lucide-music" class="w-16 h-16 mx-auto mb-4 text-neutral-400" />
      <h3 class="text-xl font-semibold mb-2">Search for music</h3>
      <p class="text-neutral-400">Find your favorite artists, albums, and tracks</p>
    </div>

    <!-- Results -->
    <div v-else class="space-y-8">
      <!-- Artists -->
      <section v-if="searchResults.artists.length > 0">
        <h2 class="text-2xl font-bold mb-4">Artists</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
          <MusicCard
            v-for="artist in searchResults.artists"
            :key="artist.id"
            :title="artist.nom"
            description="Artist"
            :image-url="artist.imageUrl ?? 'https://placehold.co/400'"
            :to="`/artist/${artist.id}`"
          />
        </div>
      </section>

      <!-- Albums -->
      <section v-if="searchResults.albums.length > 0">
        <h2 class="text-2xl font-bold mb-4">Albums</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
          <MusicCard
            v-for="album in searchResults.albums"
            :key="album.id"
            :title="album.titre"
            :description="album.artiste?.nom || 'Unknown Artist'"
            :cover-url="album.coverUrl ?? (album.artiste?.imageUrl ?? 'https://placehold.co/400')"
            :to="`/album/${album.id}`"
          />
        </div>
      </section>

      <!-- Tracks -->
      <section v-if="searchResults.tracks.length > 0">
        <h2 class="text-2xl font-bold mb-4">Tracks</h2>
        <div class="space-y-2">
          <div
            v-for="(track, index) in searchResults.tracks"
            :key="track.id"
            class="flex items-center gap-4 p-3 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800/50 cursor-pointer group"
            @click="playTrack(track)"
          >
            <div class="flex items-center justify-center w-10 text-neutral-400 group-hover:text-white">
              <span class="group-hover:hidden">{{ index + 1 }}</span>
              <UIcon name="i-lucide-play" class="hidden group-hover:block w-5 h-5" />
            </div>
            <img
              :src="prefixApiResource(track.album?.coverUrl || track.artiste?.imageUrl || 'https://placehold.co/400')"
              class="w-12 h-12 rounded object-cover"
            />
            <div class="flex-1 min-w-0">
              <div class="font-semibold truncate">{{ track.titre }}</div>
              <div class="text-sm text-neutral-400 truncate">
                {{ track.artiste?.nom || 'Unknown Artist' }}
              </div>
            </div>
            <div class="text-sm text-neutral-400">
              {{ track.album?.titre || '' }}
            </div>
            <div class="text-sm text-neutral-400 w-12 text-right">
              {{ Math.floor(track.duree / 60) }}:{{ String(track.duree % 60).padStart(2, '0') }}
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
