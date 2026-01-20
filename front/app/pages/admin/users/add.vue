<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

const router = useRouter()

const onSubmit = async (formData: FormData) => {
    try {
        await api.request('/api/admin/users', {
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
        <h2 class="text-2xl font-bold mb-4">Add User</h2>
        <FormUser @submit="onSubmit" @cancel="router.push('/admin/users')" />
    </UCard>
</template>
