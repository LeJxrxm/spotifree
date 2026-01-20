<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

interface User {
  id: number
  email: string
  pseudo: string
  avatarUrl?: string
  roles: string[]
}

const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'pseudo', header: 'Pseudo' },
    { accessorKey: 'email', header: 'Email' },
    { accessorKey: 'roles', header: 'Roles' },
    { accessorKey: 'avatarUrl', header: 'Avatar' },
    { id: 'actions', header: 'Actions' }
]

const users = ref<User[]>([]);
const loading = ref(false);

const refresh = async () => {
    loading.value = true;
    try {
        users.value = await api.request('/api/admin/users') as User[];
    } finally {
        loading.value = false;
    }
}

const deleteUser = async (id: number) => {
    if(!confirm('Are you sure?')) return;
    try {
        await api.request(`/api/admin/users/${id}`, { method: 'DELETE' });
        refresh();
    } catch (e) {
        alert('Error deleting user');
    }
}

onMounted(() => {
    refresh();
})
</script>

<template>
  <UCard>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Users</h2>
        <UButton icon="i-heroicons-plus" to="/admin/users/add">Add User</UButton>
    </div>

    <UTable :data="users" :columns="columns" :loading="loading">
         <template #avatarUrl-cell="{ row }">
            <UAvatar v-if="row.original.avatarUrl" :src="prefixApiResource(row.original.avatarUrl)" size="sm" />
            <span v-else class="text-gray-400">No avatar</span>
        </template>
        <template #roles-cell="{ row }">
            <UBadge v-for="role in row.original.roles" :key="role" color="neutral" variant="soft" class="mr-1">
                {{ role }}
            </UBadge>
        </template>
        <template #actions-cell="{ row }">
            <div class="flex gap-2">
                <UButton icon="i-heroicons-pencil" color="neutral" variant="ghost" size="xs" :to="`/admin/users/${row.original.id}`" />
                <UButton icon="i-heroicons-trash" color="error" variant="ghost" size="xs" @click="deleteUser(row.original.id)" />
            </div>
        </template>
    </UTable>
  </UCard>
</template>
