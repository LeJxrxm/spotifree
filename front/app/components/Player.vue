<script setup lang="ts">
import { usePlayerStore } from '~/stores/player'
import type { Artiste } from '~/types/Artiste'
import type { Musique } from '~/types/Musique'

const playerStore = usePlayerStore()
const { currentTrack, isPlaying, volume } = storeToRefs(playerStore)
const { updateMediaSession, updatePlaybackState, updatePosition } = useMediaSession()

const audioPlayer = ref<HTMLAudioElement | null>(null)
const currentTime = ref(0)
const duration = ref(0)
const progress = ref(0) // 0-100

// Helper to normalize URLs
const getResourceUrl = (path: string | undefined) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return prefixApiResource(path)
}

const getTrackImage = (track: Musique | null) => {
    if (!track) return ''
    // Try album cover, then artist image, then nothing
    return track.album?.coverUrl || track.artiste?.imageUrl || (track.artiste as Artiste)?.imageUrl
}

// Watchers
watch(currentTrack, async (newTrack) => {
  if (newTrack) {
    await nextTick()
    if (audioPlayer.value) {
      audioPlayer.value.src = getResourceUrl(newTrack.audioUrl)
      audioPlayer.value.load()
      
      // Mise à jour Media Session pour écran verrouillé
      updateMediaSession(newTrack)
      
      if (isPlaying.value) {
        audioPlayer.value.play().catch(e => console.error("Playback failed", e))
      }
    }
  }
})

watch(isPlaying, async (playing) => {
  if (playing && !audioPlayer.value) await nextTick()
  if (!audioPlayer.value) return
  
  // Mise à jour de l'état de lecture pour écran verrouillé
  updatePlaybackState(playing ? 'playing' : 'paused')
  
  if (playing) {
    audioPlayer.value.play().catch(e => console.error("Playback failed", e))
  } else {
    audioPlayer.value.pause()
  }
})

watch(volume, (vol) => {
  if (audioPlayer.value) {
    audioPlayer.value.volume = vol
  }
})

// Audio Events
const onTimeUpdate = () => {
  
  // Mise à jour de la position pour écran verrouillé
  if (duration.value) {
    updatePosition(duration.value, currentTime.value)
  }
  if (!audioPlayer.value) return
  currentTime.value = audioPlayer.value.currentTime
  progress.value = (audioPlayer.value.currentTime / audioPlayer.value.duration) * 100
}

const onLoadedMetadata = () => {
  if (!audioPlayer.value) return
  duration.value = audioPlayer.value.duration
}

const onEnded = () => {
  playerStore.pause()
  currentTime.value = 0
  progress.value = 0
}

const seek = (e: Event) => {
  const target = e.target as HTMLInputElement
  const val = Number(target.value)
  if (audioPlayer.value && duration.value) {
    audioPlayer.value.currentTime = (val / 100) * duration.value
  }
}

const formatTime = (seconds: number) => {
  if (!seconds || isNaN(seconds)) return '0:00'
  const m = Math.floor(seconds / 60)
  const s = Math.floor(seconds % 60)
  return `${m}:${s < 10 ? '0' : ''}${s}`
}

// Écouter les événements Media Session pour suivant/précédent
onMounted(() => {
  window.addEventListener('mediaSessionNext', () => {
    // TODO: Implémenter lecture piste suivante
    console.log('Next track requested from lock screen')
  })
  
  window.addEventListener('mediaSessionPrevious', () => {
    // TODO: Implémenter lecture piste précédente
    console.log('Previous track requested from lock screen')
  })
})

onUnmounted(() => {
  window.removeEventListener('mediaSessionNext', () => {})
  window.removeEventListener('mediaSessionPrevious', () => {})
})
</script>

<template>
  <div v-if="currentTrack" class="fixed bottom-0 md:bottom-0 left-0 right-0 bg-black/95 border-t border-white/10 z-50 text-white mb-[72px] md:mb-0">
    <!-- Hidden Audio Element -->
    <audio
      ref="audioPlayer"
      @timeupdate="onTimeUpdate"
      @loadedmetadata="onLoadedMetadata"
      @ended="onEnded"
      class="hidden"
    ></audio>

    <!-- Mobile Layout -->
    <div class="md:hidden px-4 py-2">
      <!-- Progress Bar (top) -->
      <div class="flex items-center gap-2 mb-2 text-xs font-variant-numeric tabular-nums text-gray-400">
        <div class="flex-1 group h-1 relative flex items-center">
          <input 
            type="range" 
            min="0" 
            max="100" 
            :value="progress"
            @input="seek"
            class="absolute w-full h-1 bg-gray-600 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-3 [&::-webkit-slider-thumb]:h-3 [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:rounded-full transition-all accent-white z-20"
          />
          <div class="w-full h-1 bg-gray-600 rounded-full overflow-hidden relative pointer-events-none">
            <div class="h-full bg-white rounded-full" :style="{ width: `${progress}%` }"></div>
          </div>
        </div>
      </div>

      <!-- Track Info + Controls -->
      <div class="flex items-center gap-3">
        <NuxtLink v-if="currentTrack.album?.id" :to="`/album/${currentTrack.album.id}`" class="flex-shrink-0">
          <img :src="getResourceUrl(getTrackImage(currentTrack))" alt="Album Art" class="w-12 h-12 object-cover rounded shadow-md hover:scale-105 transition-transform" />
        </NuxtLink>
        <img v-else :src="getResourceUrl(getTrackImage(currentTrack))" alt="Album Art" class="w-12 h-12 object-cover rounded shadow-md flex-shrink-0" />
        
        <div class="flex flex-col min-w-0 flex-1">
          <h3 class="text-sm font-semibold truncate">{{ currentTrack.titre }}</h3>
          <NuxtLink v-if="currentTrack.artiste?.id" :to="`/artist/${currentTrack.artiste.id}`" class="text-xs text-gray-400 hover:text-white hover:underline truncate block">
            {{ currentTrack.artiste.nom }}
          </NuxtLink>
          <p v-else class="text-xs text-gray-400 truncate">{{ currentTrack.artiste?.nom }}</p>
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-2 flex-shrink-0">
          <UButton 
            :icon="isPlaying ? 'i-lucide-pause' : 'i-lucide-play'" 
            variant="ghost" 
            color="neutral" 
            size="lg"
            class="text-white"
            @click="playerStore.togglePlay()"
          />
        </div>
      </div>
    </div>

    <!-- Desktop Layout -->
    <div class="hidden md:grid md:grid-cols-3 gap-4 px-4 py-3 h-24 items-center">
      <!-- Left: Track Info -->
      <div class="flex items-center gap-4 min-w-0">
        <NuxtLink v-if="currentTrack.album?.id" :to="`/album/${currentTrack.album.id}`">
          <img :src="getResourceUrl(getTrackImage(currentTrack))" alt="Album Art" class="w-14 h-14 object-cover rounded shadow-md hover:scale-105 transition-transform" />
        </NuxtLink>
        <img v-else :src="getResourceUrl(getTrackImage(currentTrack))" alt="Album Art" class="w-14 h-14 object-cover rounded shadow-md" />
        
        <div class="flex flex-col min-w-0 justify-center">
          <h3 class="text-sm font-bold truncate">{{ currentTrack.titre }}</h3>
          <div class="flex items-center gap-1 min-w-0">
            <NuxtLink v-if="currentTrack.artiste?.id" :to="`/artist/${currentTrack.artiste.id}`" class="text-xs text-gray-400 hover:underline hover:text-white transition-colors truncate">
              {{ currentTrack.artiste.nom }}
            </NuxtLink>
            <p v-else class="text-xs text-gray-400 truncate">
              {{ currentTrack.artiste?.nom }}
            </p>
            <template v-if="currentTrack.album?.id">
              <span class="text-xs text-gray-400">•</span>
              <NuxtLink :to="`/album/${currentTrack.album.id}`" class="text-xs text-gray-400 hover:underline hover:text-white transition-colors truncate">
                {{ currentTrack.album.titre }}
              </NuxtLink>
            </template>
          </div>
        </div>
        <UButton icon="i-lucide-circle-plus" variant="ghost" color="neutral" class="text-gray-400 hover:text-white" :ui="{ rounded: 'rounded-full' }" />
      </div>

      <!-- Center: Controls & Progress -->
      <div class="flex flex-col items-center gap-2 w-full max-w-2xl justify-self-center">
        <!-- Player Controls -->
        <div class="flex items-center gap-6">
          <UButton icon="i-lucide-shuffle" variant="ghost" color="neutral" class="text-gray-400 hover:text-white" disabled />
          <UButton icon="i-lucide-skip-back" variant="ghost" color="neutral" class="text-gray-400 hover:text-white" />
          
          <UButton 
            :icon="isPlaying ? 'i-lucide-pause' : 'i-lucide-play'" 
            variant="solid" 
            color="neutral" 
            class="rounded-full w-8 h-8 flex items-center justify-center text-black hover:scale-105 transition-transform"
            :ui="{ rounded: 'rounded-full', color: { white: { solid: 'bg-white text-black hover:bg-white' } } }"
            @click="playerStore.togglePlay()"
          />

          <UButton icon="i-lucide-skip-forward" variant="ghost" color="neutral" class="text-gray-400 hover:text-white" />
          <UButton icon="i-lucide-repeat" variant="ghost" color="neutral" class="text-gray-400 hover:text-white" disabled />
        </div>

        <!-- Scrollbar -->
        <div class="flex items-center gap-2 w-full text-xs font-variant-numeric tabular-nums text-gray-400">
          <span class="w-8 text-right">{{ formatTime(currentTime) }}</span>
          <div class="flex-1 group h-1 relative flex items-center">
            <input 
              type="range" 
              min="0" 
              max="100" 
              :value="progress"
              @input="seek"
              class="absolute w-full h-1 bg-gray-600 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-0 [&::-webkit-slider-thumb]:h-0 hover:[&::-webkit-slider-thumb]:w-3 hover:[&::-webkit-slider-thumb]:h-3 hover:[&::-webkit-slider-thumb]:bg-white hover:[&::-webkit-slider-thumb]:rounded-full hover:[&::-webkit-slider-thumb]:appearance-auto transition-all accent-white z-20 opacity-0 group-hover:opacity-100"
            />
            <!-- Custom visual track -->
            <div class="w-full h-1 bg-gray-600 rounded-full overflow-hidden relative">
                <div class="h-full bg-white rounded-full group-hover:bg-green-500" :style="{ width: `${progress}%` }"></div>
            </div>
          </div>
          <span class="w-8">{{ formatTime(duration) }}</span>
        </div>
      </div>

      <!-- Right: Volume & Options -->
      <div class="flex items-center gap-2 justify-end">
        <UButton icon="i-lucide-mic-2" variant="ghost" color="neutral" class="text-gray-400 hover:text-white" />
        <UButton icon="i-lucide-list-music" variant="ghost" color="neutral" class="text-gray-400 hover:text-white" />
        <UButton icon="i-lucide-monitor-speaker" variant="ghost" color="neutral" class="text-gray-400 hover:text-white" />
        
        <div class="flex items-center gap-2 w-24">
            <UIcon name="i-lucide-volume-2" class="w-5 h-5 text-gray-400" />
            <input 
              type="range" 
              min="0" 
              max="1" 
              step="0.01" 
              v-model="volume"
              class="w-full h-1 bg-gray-600 rounded-lg appearance-none cursor-pointer accent-white hover:accent-green-500"
            />
        </div>

        <UButton icon="i-lucide-maximize-2" variant="ghost" color="neutral" class="text-gray-400 hover:text-white" />
      </div>
    </div>
  </div>
</template>
