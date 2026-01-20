const api = {
  async request<T>(url: string, options: any = {}): Promise<T> {
    const config = useRuntimeConfig();
    const baseURL = config.public.apiBase;

    const token = useCookie("token").value;

    const headers: Record<string, string> = {
      "Content-Type": "application/json",
      ...options.headers,
    };

    if (token && !headers.Authorization) {
      headers.Authorization = `Bearer ${token}`;
    }

    try {
      const response = await $fetch<T>(url, {
        baseURL,
        method: options.method || "GET",
        headers,
        body: options.body,
        ...options,
      });

      return response;
    } catch (error: any) {
      // Handle 401 with refresh token
      if (error.response?.status === 401 && !options._isRetry) {
        const refreshToken = useCookie("refresh_token").value;
        if (refreshToken) {
          try {
            const refreshResponse = await $fetch<{ token: string; refresh_token: string }>("/api/token/refresh", {
              baseURL,
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: { refresh_token: refreshToken },
            });

            useCookie("token").value = refreshResponse.token;
            if (refreshResponse.refresh_token) {
              useCookie("refresh_token").value = refreshResponse.refresh_token;
            }

            // Retry original request
            return await this.request<T>(url, { ...options, _isRetry: true });
          } catch (refreshError) {
            // Refresh failed, redirect to login
            if (typeof window !== "undefined") {
              window.location.href = "/auth/login";
            }
            throw error;
          }
        }
      }
      throw error;
    }
  },

  get<T>(url: string, options?: any): Promise<T> {
    return this.request<T>(url, { ...options, method: "GET" });
  },

  post<T>(url: string, body?: any, options?: any): Promise<T> {
    return this.request<T>(url, { ...options, method: "POST", body });
  },

  put<T>(url: string, body?: any, options?: any): Promise<T> {
    return this.request<T>(url, { ...options, method: "PUT", body });
  },

  patch<T>(url: string, body?: any, options?: any): Promise<T> {
    return this.request<T>(url, { ...options, method: "PATCH", body });
  },

  delete<T>(url: string, options?: any): Promise<T> {
    return this.request<T>(url, { ...options, method: "DELETE" });
  },
};

const prefixApiResource = (path: string) => {
  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }

  const apiBase = useRuntimeConfig().public.apiBase;
  return `${apiBase}${path}`;
};

export default api;
export {prefixApiResource}
