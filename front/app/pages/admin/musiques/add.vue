<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

const router = useRouter()
const artists = ref<any[]>([])
const albums = ref<any[]>([])

onMounted(async () => {
    const [artistsData, albumsData] = await Promise.all([
        api.request('/api/admin/artistes') as Promise<any[]>,
        api.request('/api/admin/albums') as Promise<any[]>
    ])
    artists.value = artistsData
    albums.value = albumsData
})

const onSubmit = async (data: FormData | any, mode: 'file' | 'youtube') => {
    try {
        if (mode === 'youtube') {
            // YouTube mode - send JSON
            await api.request('/api/musiques/youtube', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
        } else {
            // File mode - send FormData
            await api.request('/api/admin/musiques', {
                method: 'POST',
                body: data
            });
        }
        router.push('/admin/musiques');
    } catch (e) {
        console.error(e);
        alert('Error saving track');
    }
}
</script>

<template>
    <UCard>
        <h2 class="text-2xl font-bold mb-4">Add Track</h2>
        <FormMusique :artists="artists" :albums="albums" @submit="onSubmit" @cancel="router.push('/admin/musiques')" />
    </UCard>
</template>
