import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../components/HomePage.vue'
import LoginPage from '../components/LoginPage.vue'
import RegisterPage from '../components/RegisterPage.vue'
import SubjectDetailPage from '../components/SubjectDetailPage.vue'
import TaskDetailEditor from '../components/TaskDetailEditor.vue'
import LookoutPage from '../components/LookoutPage.vue'
import { useAuth } from '../composables/useAuth'
const routes = [
    {
        path: '/',
        name: 'Home',
        component: HomePage,
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'Login',
        component: LoginPage
    },
    {
        path: '/register',
        name: 'Register',
        component: RegisterPage
    },
    {
        path: '/subject/:id',
        name: 'SubjectDetail',
        component: SubjectDetailPage,
        meta: { requiresAuth: true }
    },
    {
        path: '/tasks/:id',
        name: 'TaskDetail',
        component: TaskDetailEditor,
        meta: { requiresAuth: true }
    },
    {
        path: '/user',
        name: 'User',
        component: LookoutPage,
        meta: { requiresAuth: true }
    }
]

export const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes
})
const auth = useAuth()

router.beforeEach((to, _from) => {
    if (to.meta.requiresAuth && !auth.isAuthenticated.value) {
        return { name: 'Login', query: { redirect: to.fullPath } }
    }

    if (to.name === 'Login' && auth.isAuthenticated.value) {
        return { name: 'Home' }
    }

    return true
})
