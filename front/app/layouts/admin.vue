<template>
  <div class="min-h-screen flex flex-col">
    <header class="bg-gray-800 text-white p-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">Admin Panel</h1>
        <div class="flex gap-4">
            <NuxtLink to="/" class="hover:underline">Back to Site</NuxtLink>
            <button @click="logout" class="hover:underline text-red-400">Logout</button>
        </div>
    </header>
    <div class="flex flex-1">
        <aside class="w-64 bg-gray-100 dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 p-4">
            <nav class="flex flex-col gap-2">
                <UButton to="/admin" variant="ghost" icon="i-heroicons-home" class="justify-start">Dashboard</UButton>
                <UButton to="/admin/artistes" variant="ghost" icon="i-heroicons-microphone" class="justify-start">Artistes</UButton>
                <UButton to="/admin/albums" variant="ghost" icon="i-heroicons-musical-note" class="justify-start">Albums</UButton>
                <UButton to="/admin/musiques" variant="ghost" icon="i-heroicons-play" class="justify-start">Musiques</UButton>
                <UButton to="/admin/users" variant="ghost" icon="i-heroicons-users" class="justify-start">Users</UButton>
            </nav>
        </aside>

        <main class="flex-1 p-6 overflow-auto">
            <slot />
        </main>
    </div>
  </div>
</template>

<script setup lang="ts">
const auth = useAuthStore();
const logout = () => {
    auth.logout();
}
</script>
