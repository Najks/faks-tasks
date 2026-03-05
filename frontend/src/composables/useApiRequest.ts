import { ref } from 'vue'
import axios, { type AxiosRequestConfig, type AxiosResponse } from 'axios'
import { api } from '../services/api'

const getErrorMessage = (error: unknown, fallback?: string) => {
  if (axios.isAxiosError(error)) {
    const serverMessage = error.response?.data?.message
    const validationErrors = error.response?.data?.errors
    if (typeof serverMessage === 'string' && serverMessage.length) {
      return serverMessage
    }
    if (validationErrors && typeof validationErrors === 'object') {
      const key = Object.keys(validationErrors)[0]
      if (key && Array.isArray(validationErrors[key]) && validationErrors[key].length) {
        return validationErrors[key][0]
      }
    }
    if (error.message) {
      return error.message
    }
  } else if (error instanceof Error) {
    return error.message
  }

  return fallback ?? 'Request failed'
}

export const useApiRequest = () => {
  const loading = ref(false)
  // allow structured server error objects (e.g. { message, errors }) as well as strings
  const error = ref<any>(null)

  const execute = async <T>(
    factory: () => Promise<AxiosResponse<T>>,
    fallbackMessage?: string
  ) => {
    loading.value = true
    error.value = null
    try {
      const response = await factory()
      return response.data
    } catch (err) {
      // If server returned structured JSON (axios), keep the full response.data so callers can inspect `message` and `errors`.
      if (axios.isAxiosError(err) && err.response?.data) {
        error.value = err.response.data
      } else {
        error.value = getErrorMessage(err, fallbackMessage)
      }
      throw err
    } finally {
      loading.value = false
    }
  }

  const request = <T>(
    config: AxiosRequestConfig,
    fallbackMessage?: string
  ) => execute(() => api.request<T>(config), fallbackMessage)

  const get = <T>(
    url: string,
    config?: AxiosRequestConfig,
    fallbackMessage?: string
  ) => execute(() => api.get<T>(url, config), fallbackMessage)

  const post = <T>(
    url: string,
    data?: unknown,
    config?: AxiosRequestConfig,
    fallbackMessage?: string
  ) => execute(() => api.post<T>(url, data, config), fallbackMessage)

  const patch = <T>(
    url: string,
    data?: unknown,
    config?: AxiosRequestConfig,
    fallbackMessage?: string
  ) => execute(() => api.patch<T>(url, data, config), fallbackMessage)

  const del = <T>(
    url: string,
    config?: AxiosRequestConfig,
    fallbackMessage?: string
  ) => execute(() => api.delete<T>(url, config), fallbackMessage)

  return {
    loading,
    error,
    execute,
    request,
    get,
    post,
    patch,
    del
  }
}
