// src/services/api.ts
import axios from 'axios'

const createApiClient = () => {
    const instance = axios.create({
        baseURL: import.meta.env.VITE_API_URL || '/api',
        timeout: 10000,
        headers: {
            'Content-Type': 'application/json'
        }
    })

    instance.interceptors.request.use((config) => {
        console.log('API request', config.method?.toUpperCase(), config.url, config.data)
        const token = localStorage.getItem('authToken')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    })

    instance.interceptors.response.use(
        (response) => {
            console.log('API response', response.status, response.config.url, response.data)
            return response
        },
        (error) => {
            console.error('API response error', error.response?.status, error.config?.url, error.response?.data)
            if (error.response?.status === 401) {
                localStorage.removeItem('authToken')
                window.location.href = '/login'
            }
            return Promise.reject(error)
        }
    )

    return instance
}

const api = createApiClient()

export { api }

export const setAuthToken = (token: string) => {
    localStorage.setItem('authToken', token)
    api.defaults.headers.common.Authorization = `Bearer ${token}`
}

export const clearAuthToken = () => {
    localStorage.removeItem('authToken')
    delete api.defaults.headers.common.Authorization
}
