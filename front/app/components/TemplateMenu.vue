<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'
import type { Artiste } from '~/types/Artiste'
import type { Album } from '~/types/Album'
import type { Musique } from '~/types/Musique'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const playerStore = usePlayerStore()

const searchQuery = ref('')
const showSuggestions = ref(false)
const isSearching = ref(false)
const suggestions = ref<{
  artists: Artiste[]
  albums: Album[]
  tracks: Musique[]
}>({
  artists: [],
  albums: [],
  tracks: []
})

let debounceTimer: NodeJS.Timeout | null = null

const logout = () => {
  auth.logout()
}

const fetchSuggestions = async () => {
  if (searchQuery.value.length < 2) {
    suggestions.value = { artists: [], albums: [], tracks: [] }
    showSuggestions.value = false
    return
  }

  isSearching.value = true
  try {
    const [artists, albums, tracks] = await Promise.all([
      api.get<Artiste[]>(`/api/artistes?search=${encodeURIComponent(searchQuery.value)}`).catch(() => []),
      api.get<Album[]>(`/api/albums?search=${encodeURIComponent(searchQuery.value)}`).catch(() => []),
      api.get<Musique[]>(`/api/musiques?search=${encodeURIComponent(searchQuery.value)}`).catch(() => [])
    ])

    suggestions.value = {
      artists: (artists || []).slice(0, 3),
      albums: (albums || []).slice(0, 3),
      tracks: (tracks || []).slice(0, 3)
    }
    showSuggestions.value = true
  } catch (e) {
    console.error('Search failed', e)
  } finally {
    isSearching.value = false
  }
}

watch(searchQuery, () => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  
  if (searchQuery.value.length < 2) {
    suggestions.value = { artists: [], albums: [], tracks: [] }
    showSuggestions.value = false
    return
  }

  debounceTimer = setTimeout(() => {
    fetchSuggestions()
  }, 300)
})

const selectSuggestion = (path: string) => {
  showSuggestions.value = false
  searchQuery.value = ''
  router.push(path)
}

const playTrack = (track: Musique) => {
  showSuggestions.value = false
  searchQuery.value = ''
  playerStore.play(track)
}

const hasSuggestions = computed(() => {
  return suggestions.value.artists.length > 0 ||
    suggestions.value.albums.length > 0 ||
    suggestions.value.tracks.length > 0
})

const items = computed<NavigationMenuItem[]>(() => [
  {
    label: 'Home',
    to: '/',
    active: route.path === '/'
  },
  {
    label: 'Library',
    to: '/library',
  },
  {
    label: 'Upload',
    to: '/upload',
    active: route.path === '/upload'
  }
])
</script>

<template>
  <!-- Header desktop -->
  <UHeader mode="drawer" class="hidden md:flex">
    <template #left>
      <div class="flex items-center gap-2">
        <UButton icon="i-lucide-chevron-left" color="neutral" variant="ghost" class="rounded-full cursor-pointer"
          size="xl" @click="$router.back()" />
        <UButton icon="i-lucide-chevron-right" color="neutral" variant="ghost" class="rounded-full cursor-pointer"
          size="xl" @click="$router.forward()" />
      </div>
    </template>

    <template #default>
      <div class="flex items-center gap-2">
        <UButton @click="async () => { await navigateTo('/') }" icon="i-lucide-house" variant="ghost" color="neutral"
          class="rounded-full cursor-pointer" size="md" />

        <div class="hidden w-96 lg:block relative">
          <UInput 
            v-model="searchQuery" 
            icon="i-lucide-search" 
            placeholder="What do you want to play?" 
            class="w-full" 
            size="md"
            @focus="searchQuery.length >= 2 && hasSuggestions && (showSuggestions = true)"
            @blur="setTimeout(() => showSuggestions = false, 200)"
          >
            <template #trailing>
              <UIcon v-if="isSearching" name="i-lucide-loader-2" class="animate-spin text-neutral-400" />
            </template>
          </UInput>

          <!-- Suggestions Dropdown -->
          <UCard v-if="showSuggestions && hasSuggestions" class="absolute top-full mt-2 w-full max-h-96 overflow-y-auto z-50 shadow-xl">
            <div class="space-y-4">
              <!-- Artists -->
              <div v-if="suggestions.artists.length > 0">
                <div class="text-xs font-semibold text-neutral-400 uppercase mb-2">Artists</div>
                <div 
                  v-for="artist in suggestions.artists" 
                  :key="'artist-' + artist.id"
                  class="flex items-center gap-3 p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                  @click="selectSuggestion(`/artist/${artist.id}`)"
                >
                  <img :src="prefixApiResource(artist.imageUrl || 'https://placehold.co/400')" class="w-10 h-10 rounded-full object-cover" />
                  <div class="flex-1 min-w-0">
                    <div class="font-medium truncate">{{ artist.nom }}</div>
                    <div class="text-xs text-neutral-400">Artist</div>
                  </div>
                </div>
              </div>

              <!-- Albums -->
              <div v-if="suggestions.albums.length > 0">
                <div class="text-xs font-semibold text-neutral-400 uppercase mb-2">Albums</div>
                <div 
                  v-for="album in suggestions.albums" 
                  :key="'album-' + album.id"
                  class="flex items-center gap-3 p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                  @click="selectSuggestion(`/album/${album.id}`)"
                >
                  <img :src="prefixApiResource(album.coverUrl || album.artiste?.imageUrl || 'https://placehold.co/400')" class="w-10 h-10 rounded object-cover" />
                  <div class="flex-1 min-w-0">
                    <div class="font-medium truncate">{{ album.titre }}</div>
                    <div class="text-xs text-neutral-400">{{ album.artiste?.nom || 'Unknown' }}</div>
                  </div>
                </div>
              </div>

              <!-- Tracks -->
              <div v-if="suggestions.tracks.length > 0">
                <div class="text-xs font-semibold text-neutral-400 uppercase mb-2">Tracks</div>
                <div 
                  v-for="track in suggestions.tracks" 
                  :key="'track-' + track.id"
                  class="flex items-center gap-3 p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                  @click="playTrack(track)"
                >
                  <img :src="prefixApiResource(track.album?.coverUrl || track.artiste?.imageUrl || 'https://placehold.co/400')" class="w-10 h-10 rounded object-cover" />
                  <div class="flex-1 min-w-0">
                    <div class="font-medium truncate">{{ track.titre }}</div>
                    <div class="text-xs text-neutral-400">{{ track.artiste?.nom || 'Unknown' }}</div>
                  </div>
                  <UIcon name="i-lucide-play" class="w-4 h-4 text-neutral-400" />
                </div>
              </div>
            </div>
          </UCard>
        </div>
      </div>
    </template>

    <template #right>
      <UButton to="/upload" icon="i-lucide-upload" color="neutral" variant="ghost" class="text-gray-500 dark:text-neutral-400" />
      <UColorModeButton color="neutral" variant="ghost" class="text-gray-500 dark:text-neutral-400" />
      <UButton icon="i-lucide-bell" color="neutral" variant="ghost" class="text-gray-500 dark:text-neutral-400" />
      <UButton icon="i-lucide-users" color="neutral" variant="ghost" class="text-gray-500 dark:text-neutral-400" />

      <template v-if="auth.isAuthenticated">
        <UAvatar text="J" size="sm" class="bg-primary text-white font-bold cursor-pointer" @click="logout" />
      </template>
      <template v-else>
        <UButton label="Log in" to="/auth/login" color="neutral" variant="solid" class="font-bold rounded-full" />
      </template>
    </template>

    <template #body>
      <UNavigationMenu :items="items" orientation="vertical" />
    </template>
  </UHeader>

  <!-- Navigation mobile en bas -->
  <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-black/95 border-t border-white/10 z-40 pb-safe">
    <div class="flex items-center justify-around px-4 py-3">
      <NuxtLink to="/" class="flex flex-col items-center gap-1 min-w-0">
        <UIcon 
          name="i-lucide-house" 
          :class="['w-6 h-6', route.path === '/' ? 'text-white' : 'text-gray-400']"
        />
        <span :class="['text-xs', route.path === '/' ? 'text-white font-semibold' : 'text-gray-400']">
          Home
        </span>
      </NuxtLink>

      <NuxtLink to="/search" class="flex flex-col items-center gap-1 min-w-0">
        <UIcon 
          name="i-lucide-search" 
          :class="['w-6 h-6', route.path === '/search' ? 'text-white' : 'text-gray-400']"
        />
        <span :class="['text-xs', route.path === '/search' ? 'text-white font-semibold' : 'text-gray-400']">
          Search
        </span>
      </NuxtLink>

      <NuxtLink to="/upload" class="flex flex-col items-center gap-1 min-w-0">
        <UIcon 
          name="i-lucide-upload" 
          :class="['w-6 h-6', route.path === '/upload' ? 'text-white' : 'text-gray-400']"
        />
        <span :class="['text-xs', route.path === '/upload' ? 'text-white font-semibold' : 'text-gray-400']">
          Upload
        </span>
      </NuxtLink>

      <NuxtLink v-if="auth.isAuthenticated" to="/profile" class="flex flex-col items-center gap-1 min-w-0">
        <UIcon 
          name="i-lucide-user" 
          :class="['w-6 h-6', route.path === '/profile' ? 'text-white' : 'text-gray-400']"
        />
        <span :class="['text-xs', route.path === '/profile' ? 'text-white font-semibold' : 'text-gray-400']">
          Profile
        </span>
      </NuxtLink>

      <NuxtLink v-else to="/auth/login" class="flex flex-col items-center gap-1 min-w-0">
        <UIcon name="i-lucide-log-in" class="w-6 h-6 text-gray-400" />
        <span class="text-xs text-gray-400">Login</span>
      </NuxtLink>
    </div>
  </nav>
</template>
