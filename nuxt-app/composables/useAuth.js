import { ref, computed } from 'vue'

// グローバルな認証状態
const isLoggedIn = ref(false)
const currentUser = ref(null)

export const useAuth = () => {
    // ログイン状態の確認
    const checkAuthStatus = () => {
        // TODO: 後でFirebase AuthやAPI連携に置き換え
        // 現在は localStorage で簡易的に管理
        const token = import.meta.client ? localStorage.getItem('auth_token') : null
        const user = import.meta.client ? localStorage.getItem('user_data') : null
        
        isLoggedIn.value = !!token
        currentUser.value = user ? JSON.parse(user) : null
        
        return isLoggedIn.value
    }

    // ログイン処理
    const login = (userData, token = 'dummy_token') => {
        if (import.meta.client) {
            localStorage.setItem('auth_token', token)
            localStorage.setItem('user_data', JSON.stringify(userData))
        }
        
        isLoggedIn.value = true
        currentUser.value = userData
    }

    // ログアウト処理
    const logout = () => {
        if (import.meta.client) {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user_data')
        }
        
        isLoggedIn.value = false
        currentUser.value = null
    }

    // 認証が必要なアクションの実行前チェック
    const requireAuth = (callback) => {
        if (isLoggedIn.value) {
            return callback()
        } else {
            // ログインページにリダイレクト
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