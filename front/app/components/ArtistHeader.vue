<script setup lang="ts">
import type { Artiste } from '~/types/Artiste'

const props = defineProps<{
  artist: Artiste
}>()

const { extractDominantColor } = useDominantColor()
const dominantColor = ref('rgb(82, 82, 91)')

onMounted(async () => {
  const imageUrl = prefixApiResource(props.artist?.imageUrl || '')
  if (imageUrl) {
    dominantColor.value = await extractDominantColor(imageUrl)
  }
})
</script>

<template>
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
</template>
