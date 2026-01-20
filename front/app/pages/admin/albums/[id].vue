<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

const route = useRoute()
const router = useRouter()
const album = ref<any>(null)
const artists = ref<any[]>([])

onMounted(async () => {
    try {
        const [albumData, artistsData] = await Promise.all([
            api.request(`/api/admin/albums/${route.params.id}`) as Promise<any>,
            api.request('/api/admin/artistes') as Promise<any[]>
        ])
        album.value = albumData
        artists.value = artistsData
    } catch (e) {
        alert('Album not found')
        router.push('/admin/albums')
    }
})

const onSubmit = async (formData: FormData) => {
    try {
        await api.request(`/api/admin/albums/${route.params.id}`, {
            method: 'POST',
            body: formData
        });
        router.push('/admin/albums');
    } catch (e) {
        console.error(e);
        alert('Error saving album');
    }
}
</script>

<template>
    <UCard>
        <h2 class="text-2xl font-bold mb-4">Edit Album</h2>
        <FormAlbum v-if="album" :initial-data="album" :artists="artists" @submit="onSubmit" @cancel="router.push('/admin/albums')" />
        <div v-else>Loading...</div>
    </UCard>
</template>
