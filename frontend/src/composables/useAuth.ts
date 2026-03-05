// src/composables/useAuth.ts
import { ref, computed } from 'vue'
import { useApiRequest } from './useApiRequest'
import { setAuthToken, clearAuthToken } from '../services/api'

interface LoginRequest {
    email: string
    password: string
}

interface RegisterRequest {
    username: string
    name: string
    email: string
    password: string
    passwordConfirmation: string
}

interface User {
    id: number
    name: string
    email: string
}

interface AuthResponse {
    token: string
    user: User
}

const storedToken = localStorage.getItem('authToken')
const storedUser = localStorage.getItem('authUser')
const token = ref<string | null>(storedToken)
const user = ref<User | null>(storedUser ? JSON.parse(storedUser) : null)

if (storedToken) {
    setAuthToken(storedToken)
}

export const useAuth = () => {
    const { loading, error, post } = useApiRequest()
    const isAuthenticated = computed(() => !!token.value)

    const persistAuth = (data: AuthResponse) => {
        token.value = data.token
        user.value = data.user
        setAuthToken(data.token)
        localStorage.setItem('authUser', JSON.stringify(data.user))
    }

    const login = async (credentials: LoginRequest) => {
        try {
            const data = await post<AuthResponse>('/auth/login', credentials, undefined, 'Login failed')
            if (!data || !data.token) {
                const msg = 'Invalid login response from server.'
                if (error && 'value' in error) error.value = msg
                throw new Error(msg)
            }
            persistAuth(data)
            return data
        } catch (err: any) {
            // ensure error ref contains a usable message
            if (error && 'value' in error && !error.value) {
                error.value = (err && err.message) ? err.message : 'Login failed.'
            }
            throw err
        }
    }

    const register = async (credentials: RegisterRequest) => {
        try {
            const data = await post<AuthResponse>('/auth/register', credentials, undefined, 'Registration failed')
            if (!data || !data.token) {
                const msg = 'Invalid registration response from server.'
                if (error && 'value' in error) error.value = msg
                throw new Error(msg)
            }
            persistAuth(data)
            return data
        } catch (err: any) {
            if (error && 'value' in error && !error.value) {
                error.value = (err && err.message) ? err.message : 'Registration failed.'
            }
            throw err
        }
    }

    const logout = () => {
        token.value = null
        user.value = null
        clearAuthToken()
        localStorage.removeItem('authUser')
    }

    return {
        token: computed(() => token.value),
        user: computed(() => user.value),
        loading,
        error,
        isAuthenticated,
        login,
        register,
        logout
    }
}
