import { defineStore } from "pinia";
import type { User } from "~/types/User";

export const useAuthStore = defineStore("auth", () => {
  const user = ref<User | null>(null);
  const loading = ref<boolean>(false);
  const token = useCookie("token", {
    sameSite: "lax",
    secure: import.meta.env.env === "prod",
    path: "/",
    httpOnly: false,
  });

  const refreshToken = useCookie("refresh_token", {
    sameSite: "lax",
    secure: import.meta.env.env === "prod",
    path: "/",
    httpOnly: false,
  });

  const isAuthenticated = computed(() => {
    return !!token.value;
  });

  const register = async (data: { email: string; password: string; username: string }) => {
    loading.value = true;
    try {
      await api.post("/api/register", data);
      return navigateTo("/auth/login");
    } catch (error) {
      console.error("Registration failed:", error);
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const login = async (credentials: { email: string; password: string }) => {
    loading.value = true;
    try {
      const response = await api.post<{ token: string; refresh_token: string }>("/api/login_check", credentials);

      token.value = response.token;
      refreshToken.value = response.refresh_token;

      await nextTick();
      await fetchUser();

      return navigateTo("/");
    } catch (error) {
      console.error("Login failed:", error);
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const fetchUser = async () => {
    try {
      const userData = await api.get<User>("/api/me");
      user.value = userData;
    } catch (error) {
      console.error("Failed to fetch user:", error);
      logout();
    }
  };

  const logout = async () => {
    token.value = null;
    refreshToken.value = null;
    user.value = null;
    await navigateTo("/auth/login");
  };

  return {
    token,
    refreshToken,
    user,
    loading,
    isAuthenticated,
    login,
    fetchUser,
    logout,
    register,
  };
});
