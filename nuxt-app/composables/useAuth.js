import { ref, computed } from 'vue'

const isLoggedIn = ref(false)
const currentUser = ref(null)

export const useAuth = () => {
    const checkAuthStatus = () => {

        const token = import.meta.client ? localStorage.getItem('auth_token') : null
        const user = import.meta.client ? localStorage.getItem('user_data') : null

        isLoggedIn.value = !!token
        currentUser.value = user ? JSON.parse(user) : null

        return isLoggedIn.value
    }

    const login = (userData, token = 'dummy_token') => {
        if (import.meta.client) {
            localStorage.setItem('auth_token', token)
            localStorage.setItem('user_data', JSON.stringify(userData))
        }

        isLoggedIn.value = true
        currentUser.value = userData
    }

    const logout = () => {
        if (import.meta.client) {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user_data')
        }

        isLoggedIn.value = false
        currentUser.value = null
    }

    const requireAuth = (callback) => {
        if (isLoggedIn.value) {
            return callback()
        } else {
            return navigateTo('/login')
        }
    }

    return {
        isLoggedIn: computed(() => isLoggedIn.value),
        currentUser: computed(() => currentUser.value),
        checkAuthStatus,
        login,
        logout,
        requireAuth
    }
}