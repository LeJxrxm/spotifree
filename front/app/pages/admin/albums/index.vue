<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

interface Album {
  id: number
  titre: string
  coverUrl?: string
  artistName: string
  artisteId: number | null
}

const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'titre', header: 'Titre' },
    { accessorKey: 'artistName', header: 'Artiste' }, // Helper column
    { accessorKey: 'coverUrl', header: 'Cover' },
    { id: 'actions', header: 'Actions' }
]

const albums = ref<Album[]>([]);
const loading = ref(false);

const refresh = async () => {
    loading.value = true;
    try {
        const albumsData = await api.request('/api/admin/albums') as any[]
        albums.value = albumsData.map((a: any) => ({
            ...a,
            artistName: a.artiste ? a.artiste.nom : 'Unknown',
            artisteId: a.artiste ? a.artiste.id : null
        }))
    } finally {
        loading.value = false;
    }
}

const deleteAlbum = async (id: number) => {
    if(!confirm('Are you sure?')) return;
    try {
        await api.request(`/api/admin/albums/${id}`, { method: 'DELETE' });
        refresh();
    } catch (e) {
        alert('Error deleting album');
    }
}

onMounted(() => {
    refresh();
})
</script>

<template>
  <UCard>
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">Albums</h2>
      <UButton icon="i-heroicons-plus" to="/admin/albums/add">Add Album</UButton>
    </div>

    <UTable :data="albums" :columns="columns" :loading="loading">
      <template #coverUrl-cell="{ row }">
        <img v-if="row.original.coverUrl" :src="prefixApiResource(row.original.coverUrl)" class="h-10 w-10 object-cover rounded" />
        <span v-else class="text-gray-400">No cover</span>
      </template>
      <template #actions-cell="{ row }">
        <div class="flex gap-2">
          <UButton icon="i-heroicons-pencil" color="neutral" variant="ghost" size="xs" :to="`/admin/albums/${row.original.id}`" />
          <UButton icon="i-heroicons-trash" color="error" variant="ghost" size="xs" @click="deleteAlbum(row.original.id)" />
        </div>
      </template>
    </UTable>
  </UCard>
</template>
