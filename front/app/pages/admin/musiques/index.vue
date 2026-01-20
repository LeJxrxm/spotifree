<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

interface Musique {
  id: number
  titre: string
  audioUrl?: string
  artistName: string
  albumTitle: string
  artisteId: number | null
  albumId: number | null
}

const columns = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'titre', header: 'Titre' },
    { accessorKey: 'artistName', header: 'Artiste' },
    { accessorKey: 'albumTitle', header: 'Album' },
    { accessorKey: 'audio', header: 'Audio' },
    { id: 'actions', header: 'Actions' }
]

const musiques = ref<Musique[]>([]);
const loading = ref(false);

const refresh = async () => {
    loading.value = true;
    try {
        const musiquesData = await api.request('/api/admin/musiques') as any[]
        musiques.value = musiquesData.map((m: any) => ({
            ...m,
            artistName: m.artiste ? m.artiste.nom : 'Unknown',
            albumTitle: m.album ? m.album.titre : 'Single',
            artisteId: m.artiste ? m.artiste.id : null,
            albumId: m.album ? m.album.id : null
        }))
    } finally {
        loading.value = false;
    }
}

const deleteMusique = async (id: number) => {
    if(!confirm('Are you sure?')) return;
    try {
        await api.request(`/api/admin/musiques/${id}`, { method: 'DELETE' });
        refresh();
    } catch (e) {
        alert('Error deleting track');
    }
}

onMounted(() => {
    refresh();
})
</script>

<template>
  <UCard>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Musiques</h2>
        <UButton icon="i-heroicons-plus" to="/admin/musiques/add">Add Track</UButton>
    </div>

    <UTable :data="musiques" :columns="columns" :loading="loading">
         <template #audio-cell="{ row }">
            <audio controls v-if="row.original.audioUrl" :src="prefixApiResource(row.original.audioUrl)" class="h-8 max-w-50" />
        </template>
        <template #actions-cell="{ row }">
            <div class="flex gap-2">
                <UButton icon="i-heroicons-pencil" color="neutral" variant="ghost" size="xs" :to="`/admin/musiques/${row.original.id}`" />
                <UButton icon="i-heroicons-trash" color="error" variant="ghost" size="xs" @click="deleteMusique(row.original.id)" />
            </div>
        </template>
    </UTable>
  </UCard>
</template>
