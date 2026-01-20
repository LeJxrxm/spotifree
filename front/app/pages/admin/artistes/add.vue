<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

const router = useRouter()

const onSubmit = async (formData: FormData) => {
    try {
        await api.request('/api/admin/artistes', {
            method: 'POST',
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
        <h2 class="text-2xl font-bold mb-4">Add Artiste</h2>
        <FormArtiste @submit="onSubmit" @cancel="router.push('/admin/artistes')" />
    </UCard>
</template>
