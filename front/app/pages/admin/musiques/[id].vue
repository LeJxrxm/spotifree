<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

const route = useRoute()
const router = useRouter()
const musique = ref<any>(null)
const artists = ref<any[]>([])
const albums = ref<any[]>([])

onMounted(async () => {
    try {
        const [musiqueData, artistsData, albumsData] = await Promise.all([
            api.request(`/api/admin/musiques/${route.params.id}`) as Promise<any>,
            api.request('/api/admin/artistes') as Promise<any[]>,
            api.request('/api/admin/albums') as Promise<any[]>
        ])
        musique.value = musiqueData
        artists.value = artistsData
        albums.value = albumsData
    } catch (e) {
        alert('Track not found')
        router.push('/admin/musiques')
    }
})

const onSubmit = async (formData: FormData) => {
    try {
        await api.request(`/api/admin/musiques/${route.params.id}`, {
            method: 'POST',
            body: formData
        });
        router.push('/admin/musiques');
    } catch (e) {
        console.error(e);
        alert('Error saving track');
    }
}
</script>

<template>
    <UCard>
        <h2 class="text-2xl font-bold mb-4">Edit Track</h2>
        <FormMusique v-if="musique" :initial-data="musique" :artists="artists" :albums="albums" @submit="onSubmit" @cancel="router.push('/admin/musiques')" />
        <div v-else>Loading...</div>
    </UCard>
</template>
