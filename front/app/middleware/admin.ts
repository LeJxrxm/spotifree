export default defineNuxtRouteMiddleware(async (to) => {
    const auth = useAuthStore()

    if (!auth.isAuthenticated) {
        return navigateTo('/auth/login')
    }

    if (!auth.user) {
        await auth.fetchUser()
    }

    if (!auth.user || !auth.user.roles.includes('ROLE_ADMIN')) {
        return navigateTo('/')
    }
})
