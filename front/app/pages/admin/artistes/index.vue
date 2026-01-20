<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

interface Artiste {
  id: number
  nom: string
  imageUrl?: string
}

const columns = [{
  accessorKey: 'id',
  header: 'ID'
}, {
  accessorKey: 'nom',
  header: 'Nom'
}, {
  accessorKey: 'imageUrl',
  header: 'Image'
}, {
  id: 'actions',
  header: 'Actions'
}]

const artists = ref<Artiste[]>([]);
const loading = ref(false);

const refresh = async () => {
    loading.value = true;
    try {
        artists.value = await api.request('/api/admin/artistes');
    } finally {
        loading.value = false;
    }
}

const deleteArtist = async (id: number) => {
    if(!confirm('Are you sure?')) return;
    try {
        await api.request(`/api/admin/artistes/${id}`, { method: 'DELETE' });
        refresh();
    } catch (e) {
        alert('Error deleting artist');
    }
}

onMounted(() => {
    refresh();
})
</script>

<template>
  <UCard>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Artistes</h2>
        <UButton icon="i-heroicons-plus" to="/admin/artistes/add">Add Artiste</UButton>
    </div>

    <UTable :data="artists" :columns="columns" :loading="loading">
        <template #imageUrl-cell="{ row }">
            <img v-if="row.original.imageUrl" :src="prefixApiResource(row.original.imageUrl)" class="h-10 w-10 object-cover rounded" />
            <span v-else class="text-gray-400">No image</span>
        </template>
        <template #actions-cell="{ row }">
            <div class="flex gap-2">
                <UButton icon="i-heroicons-pencil" color="neutral" variant="ghost" size="xs" :to="`/admin/artistes/${row.original.id}`" />
                <UButton icon="i-heroicons-trash" color="error" variant="ghost" size="xs" @click="deleteArtist(row.original.id)" />
            </div>
        </template>
    </UTable>
  </UCard>
</template>
