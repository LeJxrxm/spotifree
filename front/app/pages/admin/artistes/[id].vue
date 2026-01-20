<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

const route = useRoute()
const router = useRouter()
const artist = ref(null)

onMounted(async () => {
    try {
        artist.value = await api.request(`/api/admin/artistes/${route.params.id}`)
    } catch (e) {
        alert('Artist not found')
        router.push('/admin/artistes')
    }
})

const onSubmit = async (formData: FormData) => {
    try {
        await api.request(`/api/admin/artistes/${route.params.id}`, {
            method: 'POST', // Using POST for file upload support in PHP
            body: formData
        });
        router.push('/admin/artistes');
    } catch (e) {
        console.error(e);
        alert('Error saving artist');
    }
}
</script>

<template>
    <UCard>
        <h2 class="text-2xl font-bold mb-4">Edit Artiste</h2>
        <FormArtiste v-if="artist" :initial-data="artist" @submit="onSubmit" @cancel="router.push('/admin/artistes')" />
        <div v-else>Loading...</div>
    </UCard>
</template>
