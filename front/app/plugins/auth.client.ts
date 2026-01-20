export default defineNuxtPlugin(async () => {
  const auth = useAuthStore();
  const token = getCookie('token');

  if (token && !auth.user) {
    try {
      await auth.fetchUser();
    } catch (error) {
      console.error('Auth initialization failed:', error);
    }
  }
});
