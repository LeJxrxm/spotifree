<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

const router = useRouter()
const artists = ref([])

onMounted(async () => {
    artists.value = await api.request('/api/admin/artistes')
})

const onSubmit = async (formData: FormData) => {
    try {
        await api.request('/api/admin/albums', {
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
        <h2 class="text-2xl font-bold mb-4">Add Album</h2>
        <FormAlbum :artists="artists" @submit="onSubmit" @cancel="router.push('/admin/albums')" />
    </UCard>
</template>
