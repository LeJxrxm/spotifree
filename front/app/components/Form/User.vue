<script setup lang="ts">
const props = defineProps<{
  initialData?: any
}>()

const emit = defineEmits(['submit', 'cancel'])

const availableRoles = ['ROLE_USER', 'ROLE_ADMIN']

const state = reactive({
  email: props.initialData?.email || '',
  pseudo: props.initialData?.pseudo || '',
  password: '',
  roles: props.initialData?.roles ? [...props.initialData.roles] : ['ROLE_USER'],
  avatar: null as File | null
})

watch(() => props.initialData, (newVal) => {
  if (newVal) {
    state.email = newVal.email
    state.pseudo = newVal.pseudo
    state.roles = [...newVal.roles]
  }
})

const onFileChange = (e: any) => {
  const file = e.target.files[0]
  if (file) state.avatar = file
}

const onSubmit = () => {
  const formData = new FormData()
  formData.append('email', state.email)
  formData.append('pseudo', state.pseudo)
  if (state.password) formData.append('password', state.password)
  
  formData.append('roles', JSON.stringify(state.roles))

  if (state.avatar) {
    formData.append('avatar', state.avatar)
  }
  
  emit('submit', formData)
}
</script>

<template>
  <form @submit.prevent="onSubmit" class="flex flex-col gap-4">
    <UFormGroup label="Email" required>
      <UInput v-model="state.email" type="email" />
    </UFormGroup>
    
    <UFormGroup label="Pseudo">
      <UInput v-model="state.pseudo" />
    </UFormGroup>

    <UFormGroup label="Password">
      <UInput v-model="state.password" type="password" placeholder="Leave empty to keep unchanged" />
    </UFormGroup>

    <UFormGroup label="Roles">
      <UCheckboxGroup 
        v-model="state.roles" 
        :items="availableRoles.map(role => ({ label: role, value: role }))"
      />
    </UFormGroup>

    <UFormGroup label="Avatar">
      <input 
        type="file" 
        @change="onFileChange" 
        accept="image/*" 
        class="block w-full text-sm text-gray-500
          file:mr-4 file:py-2 file:px-4
          file:rounded-full file:border-0
          file:text-sm file:font-semibold
          file:bg-primary-50 file:text-primary-700
          hover:file:bg-primary-100
        " 
      />
      <p v-if="initialData && initialData.avatarUrl" class="text-xs text-gray-500 mt-1">
        Current avatar: <UAvatar :src="initialData.avatarUrl" size="2xs" />
      </p>
    </UFormGroup>

    <div class="flex justify-end gap-2 mt-4">
      <UButton color="neutral" variant="ghost" @click="$emit('cancel')">Cancel</UButton>
      <UButton type="submit" color="primary">Save</UButton>
    </div>
  </form>
</template>
