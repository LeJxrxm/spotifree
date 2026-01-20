<script setup lang="ts">
const props = defineProps<{
  initialData?: any
}>()

const emit = defineEmits(['submit', 'cancel'])

const state = reactive({
  nom: props.initialData?.nom || '',
  image: null as File | null
})

// Update state if initialData changes (e.g. fetched async)
watch(() => props.initialData, (newVal) => {
  if (newVal) {
    state.nom = newVal.nom
  }
})

const previewUrl = computed(() => {
  if (state.image) {
    return URL.createObjectURL(state.image)
  }
  return null
})

const onSubmit = () => {
  const formData = new FormData()
  formData.append('nom', state.nom)
  if (state.image) {
    formData.append('image', state.image)
  }
  emit('submit', formData)
}
</script>

<template>
  <form @submit.prevent="onSubmit" class="flex flex-col gap-4">
    <UFormGroup label="Nom" required>
      <UInput v-model="state.nom" />
    </UFormGroup>

    <UFormGroup label="Image">
      <!-- Show existing image if available -->
      <div v-if="initialData && initialData.imageUrl && !state.image" class="mb-3">
        <p class="text-sm text-gray-600 mb-2">Image actuelle :</p>
        <img :src="prefixApiResource(initialData.imageUrl)" class="h-32 w-32 object-cover rounded-lg border" alt="Current image" />
      </div>
      
      <!-- Show preview of new image -->
      <div v-if="previewUrl" class="mb-3">
        <p class="text-sm text-gray-600 mb-2">Nouvelle image :</p>
        <img :src="previewUrl" class="h-32 w-32 object-cover rounded-lg border" alt="Preview" />
      </div>
      
      <UFileUpload 
        v-model="state.image" 
        accept="image/*"
        icon="i-heroicons-photo"
        label="Glissez votre image ici"
        description="PNG, JPG ou WEBP (max. 5MB)"
      />
    </UFormGroup>

    <div class="flex justify-end gap-2 mt-4">
      <UButton color="neutral" variant="ghost" @click="$emit('cancel')">Cancel</UButton>
      <UButton type="submit" color="primary">Save</UButton>
    </div>
  </form>
</template>
