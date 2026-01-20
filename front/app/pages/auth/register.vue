<script setup lang="ts">
import { z } from 'zod'
import type { FormSubmitEvent } from '#ui/types'

definePageMeta({
    layout: 'auth'
})

const schema = z.object({
    pseudo: z.string().min(3, 'Username must be at least 3 characters'),
    email: z.string().email('Invalid email'),
    password: z.string().min(6, 'Password must be at least 6 characters'),
    confirmPassword: z.string()
}).refine((data) => data.password === data.confirmPassword, {
    message: "Passwords don't match",
    path: ["confirmPassword"],
})

type Schema = z.infer<typeof schema>

const state = reactive({
    pseudo: '',
    email: '',
    password: '',
    confirmPassword: ''
})

const loading = ref(false)
const error = ref('')

const router = useRouter()
const toast = useToast()
const auth = useAuthStore()

async function onSubmit(event: FormSubmitEvent<Schema>) {
    loading.value = true
    error.value = ''

    try {
        await auth.register({
            email: event.data.email,
            password: event.data.password,
            pseudo: event.data.pseudo
        })

        toast.add({
            title: 'Account created!',
            description: 'You can now log in with your credentials.',
            color: 'success'
        })

        router.push('/auth/login')
    } catch (e: any) {
        error.value = e.data?.message || 'An error occurred during registration'
        if (e.data?.errors) {
            error.value += ': ' + e.data.errors.join(', ')
        }
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
                <h1 class="text-xl font-bold text-center">Create an account</h1>
            </template>

            <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
                <UFormField label="Username" name="pseudo">
                    <UInput class="w-full" v-model="state.pseudo" icon="i-lucide-user" placeholder="Your username"
                        autofocus />
                </UFormField>

                <UFormField label="Email" name="email">
                    <UInput class="w-full" v-model="state.email" icon="i-lucide-mail" placeholder="user@example.com" />
                </UFormField>

                <UFormField label="Password" name="password">
                    <UInput class="w-full" v-model="state.password" icon="i-lucide-lock" type="password"
                        placeholder="********" />
                </UFormField>

                <UFormField label="Confirm Password" name="confirmPassword">
                    <UInput class="w-full" v-model="state.confirmPassword" icon="i-lucide-lock" type="password"
                        placeholder="********" />
                </UFormField>

                <div v-if="error" class="text-red-500 text-sm text-center">
                    {{ error }}
                </div>

                <UButton type="submit" block :loading="loading">
                    Sign up
                </UButton>
            </UForm>

            <template #footer>
                <p class="text-sm text-center text-gray-500 dark:text-gray-400">
                    Already have an account?
                    <NuxtLink to="/auth/login" class="text-primary font-medium hover:underline">Log in</NuxtLink>
                </p>
            </template>
        </UCard>
    </div>
</template>
