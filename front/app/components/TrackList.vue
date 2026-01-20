<script setup lang="ts">
import { usePlayerStore } from '~/stores/player'
import type { Artiste } from '~/types/Artiste';
import type { Musique } from '~/types/Musique';

const props = defineProps<{
    title?: string
    tracks: Musique[]
    seeMoreText?: string
}>()

const playerStore = usePlayerStore()
const { currentTrack, isPlaying } = storeToRefs(playerStore)

const hoveredRow = ref<number | null>(null)
const isCurrentTrack = (trackId: number) => currentTrack.value?.id === trackId

const formatDuration = (seconds: number) => {
    if (!seconds) return '0:00'
    const m = Math.floor(seconds / 60)
    const s = seconds % 60
    return `${m}:${s < 10 ? '0' : ''}${s}`
}

const getTrackImage = (track: Musique) => {
    return track.album?.coverUrl || track.artiste?.imageUrl || (track.artiste as Artiste)?.imageUrl || ''
}
// Helper (duplicated temporarily to fix template scope issues if not imported)
// Better: rely on prefixApiResource imported globally
</script>

<template>
    <div>
        <h2 v-if="title" class="text-2xl font-bold mb-4">{{ title }}</h2>
        <div class="flex flex-col">
            <div
                v-for="(track, index) in tracks"
                :key="track.id"
                class="grid grid-cols-[auto_auto_1fr_auto_auto] gap-4 px-4 py-2 rounded-md hover:bg-gray-200 dark:hover:bg-white/10 group cursor-pointer items-center transition-colors"
                @mouseenter="hoveredRow = track.id"
                @mouseleave="hoveredRow = null"
                @click="playerStore.play(track)"
            >
                <div class="w-6 text-center flex items-center justify-center text-gray-500 dark:text-neutral-400 font-medium text-base">
                    <!-- Is Playing State -->
                    <span v-if="isCurrentTrack(track.id) && isPlaying" class="text-green-600 dark:text-green-500">
                        <UIcon v-if="hoveredRow === track.id" name="i-lucide-pause" class="w-4 h-4 fill-current" />
                        <UIcon v-else name="i-lucide-music-2" class="w-4 h-4" />
                    </span>
                    
                    <!-- Hover State (not playing) -->
                    <span v-else-if="hoveredRow === track.id" class="text-gray-900 dark:text-white">
                        <UIcon name="i-lucide-play" class="w-4 h-4 fill-current" />
                    </span>

                    <!-- Default State -->
                    <span v-else class="group-hover:hidden" :class="isCurrentTrack(track.id) ? 'text-green-600 dark:text-green-500' : ''">
                        {{ index + 1 }}
                    </span>
                </div>

                <div class="w-10 h-10">
                    <img :src="prefixApiResource(getTrackImage(track))" :alt="track.titre" class="w-full h-full object-cover rounded" />
                </div>

                <div class="min-w-0 font-medium truncate h-full flex items-center" 
                     :class="isCurrentTrack(track.id) ? 'text-green-600 dark:text-green-500' : (hoveredRow === track.id ? 'text-gray-900 dark:text-white' : 'text-gray-900 dark:text-white')">
                    {{ track.titre }}
                </div>

                <div class="w-12 text-right text-gray-500 dark:text-neutral-400 text-sm font-variant-numeric tabular-nums">
                    {{ formatDuration(track.duree) }}
                </div>
            </div>
        </div>
        <UButton
            v-if="seeMoreText"
            variant="link"
            color="neutral"
            class="mt-4 text-xs font-bold uppercase tracking-wider hover:underline text-gray-500 dark:text-neutral-400 ml-4"
        >
            {{ seeMoreText }}
        </UButton>
    </div>
</template>
