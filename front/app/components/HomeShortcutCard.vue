<script setup lang="ts">
defineProps<{
  label: string
  image: string
  icon?: string
}>()
</script>

<template>
  <div class="flex items-center bg-gray-100 dark:bg-gray-800/40 hover:bg-gray-200 dark:hover:bg-gray-700/40 transition rounded overflow-hidden cursor-pointer group">
    <div class="h-16 w-16 min-w-16 shadow-lg relative">
        <img :src="image" :alt="label" class="object-cover h-full w-full" />
        <div v-if="icon" class="absolute inset-0 flex items-center justify-center bg-linear-to-br from-indigo-500 to-purple-500">
          <UIcon :name="icon" class="w-8 h-8 text-white" />
        </div>
    </div>
    <span class="font-bold px-4 truncate text-gray-900 dark:text-white">{{ label }}</span>
    <div class="ml-auto mr-4 opacity-0 group-hover:opacity-100 transition shadow-xl rounded-full bg-green-500 p-2 flex items-center justify-center">
        <UIcon name="i-lucide-play" class="text-black w-5 h-5 fill-current" />
    </div>
  </div>
</template>
