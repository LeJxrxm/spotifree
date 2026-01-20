<script setup lang="ts">
const props = defineProps<{
  initialData?: any,
  artists: any[]
}>()

const emit = defineEmits(['submit', 'cancel'])

const state = reactive({
  titre: props.initialData?.titre || '',
  artiste: props.initialData?.artiste?.id || props.initialData?.artisteId || '',
  cover: null as File | null
})

watch(() => props.initialData, (newVal) => {
  if (newVal) {
    state.titre = newVal.titre
    state.artiste = newVal.artiste?.id || newVal.artisteId || ''
  }
})

const previewUrl = computed(() => {
  if (state.cover) {
    return URL.createObjectURL(state.cover)
  }
  return null
})

const onSubmit = () => {
  const formData = new FormData()
  formData.append('titre', state.titre)
  formData.append('artiste', state.artiste)
  if (state.cover) {
    formData.append('cover', state.cover)
  }
  emit('submit', formData)
}
</script>

<template>
  <form @submit.prevent="onSubmit" class="flex flex-col gap-4">
    <UFormGroup label="Titre" required>
      <UInput v-model="state.titre" />
    </UFormGroup>
    
    <UFormGroup label="Artiste" required>
      <USelect 
        v-model="state.artiste" 
        :items="artists.map((a: any) => ({ label: a.nom, value: a.id }))" 
        placeholder="Sélectionner un artiste"
      />
    </UFormGroup>

    <UFormGroup label="Cover Image">
      <!-- Show existing cover if available -->
      <div v-if="initialData && initialData.coverUrl && !state.cover" class="mb-3">
        <p class="text-sm text-gray-600 mb-2">Cover actuelle :</p>
        <img :src="prefixApiResource(initialData.coverUrl)" class="h-32 w-32 object-cover rounded-lg border" alt="Current cover" />
      </div>
      
      <!-- Show preview of new cover -->
      <div v-if="previewUrl" class="mb-3">
        <p class="text-sm text-gray-600 mb-2">Nouvelle cover :</p>
        <img :src="previewUrl" class="h-32 w-32 object-cover rounded-lg border" alt="Preview" />
      </div>
      
      <UFileUpload 
        v-model="state.cover" 
        accept="image/*"
        icon="i-heroicons-photo"
        label="Glissez votre cover ici"
        description="PNG, JPG ou WEBP (max. 5MB)"
      />
    </UFormGroup>

    <div class="flex justify-end gap-2 mt-4">
      <UButton color="neutral" variant="ghost" @click="$emit('cancel')">Cancel</UButton>
      <UButton type="submit" color="primary">Save</UButton>
    </div>
  </form>
</template>
