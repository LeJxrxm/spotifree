<script setup lang="ts">
import { z } from 'zod'
import type { FormSubmitEvent } from '#ui/types'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
    layout: 'auth'
})

const schema = z.object({
    email: z.string().email(),
    password: z.string().min(4)
})

type Schema = z.infer<typeof schema>

const state = reactive({
    email: '',
    password: ''
})

const loading = ref(false)
const error = ref('')

const auth = useAuthStore()

async function onSubmit(event: FormSubmitEvent<Schema>) {
    loading.value = true
    error.value = ''

    try {
        await auth.login(event.data)
    } catch (e: any) {
        error.value = 'Invalid credentials'
        console.error(e)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-ui-bg p-4">
        <UCard class="w-full max-w-sm">
            <template #header>
                <h1 class="text-xl font-bold text-center">Login</h1>
            </template>

            <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
                <UFormField label="Email" name="email">
                    <UInput class="w-full" v-model="state.email" icon="i-lucide-mail" placeholder="user@example.com"
                        autofocus />
                </UFormField>

                <UFormField label="Password" name="password">
                    <UInput class="w-full" v-model="state.password" icon="i-lucide-lock" type="password"
                        placeholder="********" />
                </UFormField>

                <div v-if="error" class="text-red-500 text-sm text-center">
                    {{ error }}
                </div>

                <UButton type="submit" block :loading="loading">
                    Sign in
                </UButton>
            </UForm>

            <template #footer>
                <p class="text-sm text-center text-gray-500 dark:text-gray-400">
                    Don't have an account?
                    <NuxtLink to="/auth/register" class="text-primary font-medium hover:underline">Sign up</NuxtLink>
                </p>
            </template>
        </UCard>
    </div>
</template>
