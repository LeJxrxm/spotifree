<script setup lang="ts">
defineProps<{
    title: string
    description: string
    coverUrl?: string // made optional since API might not always return it immediately if not mapped correctly, but safer to keep string. Entity has string|null.
    imageUrl?: string // Backup if card is used for artist
    to?: string
}>()
import { prefixApiResource } from '~/utils/api'
</script>

<template>
  <UCard :to="to" class="bg-gray-100 dark:bg-gray-800/40 hover:bg-gray-200 dark:hover:bg-gray-700/40 border-none transition group cursor-pointer block" :ui="{ body: { padding: 'p-4' } }">
    <div class="relative mb-4 shadow rounded-md overflow-hidden aspect-square">
      <NuxtLink :to="to" class="absolute inset-0 z-10" />
      <img :src="prefixApiResource(coverUrl || imageUrl)" :alt="title" class="object-cover w-full h-full" />
      <div
        class="absolute bottom-2 right-2 translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 shadow-xl rounded-full bg-green-500 p-3 flex items-center justify-center transform hover:scale-105">
        <UIcon name="i-lucide-play" class="text-black w-6 h-6 fill-current" />
      </div>
    </div>
    <NuxtLink :to="to" class="no-underline">
      <h3 class="font-bold text-gray-900 dark:text-white truncate mb-1">{{ title }}</h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ description }}</p>
    </NuxtLink>
  </UCard>
</template>
