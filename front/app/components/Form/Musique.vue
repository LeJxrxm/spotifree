<script setup lang="ts">
const props = defineProps<{
  initialData?: any,
  artists: any[],
  albums: any[]
}>()

const emit = defineEmits(['submit', 'cancel'])

const uploadMode = ref<'file' | 'youtube'>('file')

const state = reactive({
  titre: props.initialData?.titre || '',
  artiste: props.initialData?.artiste?.id || props.initialData?.artisteId || '',
  album: props.initialData?.album?.id || props.initialData?.albumId || '',
  file: null as File | null,
  youtubeUrl: ''
})

watch(() => props.initialData, (newVal) => {
  if (newVal) {
    state.titre = newVal.titre
    state.artiste = newVal.artiste?.id || newVal.artisteId || ''
    state.album = newVal.album?.id || newVal.albumId || ''
  }
})

const filteredAlbums = computed(() => {
    if (!state.artiste) return props.albums
    return props.albums.filter((a: any) => a.artiste && a.artiste.id == state.artiste)
})

const onSubmit = () => {
  if (uploadMode.value === 'youtube') {
    // YouTube upload - send JSON
    const payload = {
      youtubeUrl: state.youtubeUrl,
      titre: state.titre || undefined,
      artiste: state.artiste || undefined,
      album: state.album || undefined
    }
    emit('submit', payload, 'youtube')
  } else {
    // File upload - send FormData
    const formData = new FormData()
    formData.append('titre', state.titre)
    if (state.artiste) formData.append('artiste', state.artiste)
    if (state.album) formData.append('album', state.album)
    
    if (state.file) {
      formData.append('file', state.file)
    }
    emit('submit', formData, 'file')
  }
}
</script>

<template>
  <form @submit.prevent="onSubmit" class="flex flex-col gap-4">
    <UFormGroup label="Titre" :required="uploadMode === 'file'">
      <UInput v-model="state.titre" :placeholder="uploadMode === 'youtube' ? 'Optionnel - sera détecté automatiquement' : 'Titre du morceau'" />
    </UFormGroup>
    
    <UFormGroup label="Artiste">
      <USelect 
        v-model="state.artiste" 
        :items="artists.map((a: any) => ({ label: a.nom, value: a.id }))" 
        placeholder="Select Artiste" 
      />
    </UFormGroup>

    <UFormGroup label="Album">
      <USelect 
        v-model="state.album" 
        :items="filteredAlbums.map((a: any) => ({ label: a.titre, value: a.id }))" 
        placeholder="Select Album (Optional)" 
      />
    </UFormGroup>

    <!-- Upload Mode Tabs -->
    <UFormGroup label="Source">
      <UTabs 
        v-model="uploadMode" 
        :items="[
          { label: 'Fichier local', value: 'file', icon: 'i-heroicons-musical-note' },
          { label: 'Lien YouTube', value: 'youtube', icon: 'i-lucide-youtube' }
        ]"
      />
    </UFormGroup>

    <!-- File Upload Mode -->
    <UFormGroup v-if="uploadMode === 'file'" label="Audio File">
      <!-- Show existing audio if available -->
      <div v-if="initialData && initialData.audioUrl && !state.file" class="mb-3">
        <p class="text-sm text-gray-600 mb-2">Fichier audio actuel :</p>
        <audio controls :src="prefixApiResource(initialData.audioUrl)" class="w-full" />
      </div>
      
      <!-- Show selected file name -->
      <div v-if="state.file" class="mb-3">
        <p class="text-sm text-gray-600 mb-2">Nouveau fichier audio :</p>
        <div class="p-2 bg-gray-100 rounded text-sm">{{ state.file.name }}</div>
      </div>
      
      <UFileUpload 
        v-model="state.file" 
        accept="audio/*"
        icon="i-heroicons-musical-note"
        label="Glissez votre fichier audio ici"
        description="MP3, WAV, FLAC ou OGG (max. 50MB)"
      />
      
      <p v-if="initialData && initialData.audioUrl" class="text-xs text-gray-500 mt-2">
        Laissez vide pour conserver le fichier actuel
      </p>
    </UFormGroup>

    <!-- YouTube URL Mode -->
    <UFormGroup v-if="uploadMode === 'youtube'" label="Lien YouTube" required>
      <UInput 
        v-model="state.youtubeUrl" 
        icon="i-lucide-youtube"
        placeholder="https://www.youtube.com/watch?v=..." 
        type="url"
      />
      <p class="text-xs text-gray-500 mt-2">
        Le titre et la durée seront détectés automatiquement depuis YouTube
      </p>
    </UFormGroup>

    <div class="flex justify-end gap-2 mt-4">
      <UButton color="neutral" variant="ghost" @click="$emit('cancel')">Cancel</UButton>
      <UButton type="submit" color="primary">
        {{ uploadMode === 'youtube' ? 'Télécharger' : 'Save' }}
      </UButton>
    </div>
  </form>
</template>
