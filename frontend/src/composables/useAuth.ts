// src/composables/useAuth.ts
import { ref, computed } from 'vue'
import { useApiRequest } from './useApiRequest'
import { setAuthToken, clearAuthToken } from '../services/api'

interface LoginRequest {
    email: string
    password: string
}

interface RegisterRequest {
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
        const data = await post<AuthResponse>('/auth/login', credentials, undefined, 'Login failed')
        persistAuth(data)
    }

    const register = async (credentials: RegisterRequest) => {
        const data = await post<AuthResponse>('/auth/register', credentials, undefined, 'Registration failed')
        persistAuth(data)
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
