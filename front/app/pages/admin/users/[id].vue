<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

const route = useRoute()
const router = useRouter()
const user = ref(null)

onMounted(async () => {
    try {
        user.value = await api.request(`/api/admin/users/${route.params.id}`)
    } catch (e) {
        alert('User not found')
        router.push('/admin/users')
    }
})

const onSubmit = async (formData: FormData) => {
    try {
        await api.request(`/api/admin/users/${route.params.id}`, {
            method: 'POST',
            body: formData
        });
        router.push('/admin/users');
    } catch (e) {
        console.error(e);
        alert('Error saving user');
    }
}
</script>

<template>
    <UCard>
        <h2 class="text-2xl font-bold mb-4">Edit User</h2>
        <FormUser v-if="user" :initial-data="user" @submit="onSubmit" @cancel="router.push('/admin/users')" />
        <div v-else>Loading...</div>
    </UCard>
</template>
