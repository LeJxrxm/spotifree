export default defineNuxtRouteMiddleware((to) => {
  const hasToken = !!useCookie('token').value;
  const publicPages = ['/auth/login', '/auth/register'];
  const isPublicPage = publicPages.includes(to.path);

  if (!hasToken && !isPublicPage) {
    return navigateTo('/auth/login');
  }

  if (hasToken && isPublicPage) {
    return navigateTo('/');
  }
});
