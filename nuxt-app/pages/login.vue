<template>
    <div class="container">
        <!-- ヘッダー -->
        <div class="header">
            <img src="/images/logo.png" alt="SHARE" class="logo" />
            <div class="nav">
                <NuxtLink to="/register" class="nav-link">新規登録</NuxtLink>
                <NuxtLink to="/login" class="nav-link">ログイン</NuxtLink>
            </div>
        </div>

        <!-- ログインフォーム -->
        <div class="form-container">
            <h2 class="form-title">ログイン</h2>
        
            <form @submit.prevent="handleLogin" class="form">
                <!-- メールアドレス -->
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="メールアドレス"
                    class="input"
                    required
                />

                <!-- パスワード -->
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="パスワード"
                    class="input"
                    required
                />

                <!-- ログインボタン -->
                <button type="submit" class="btn-primary" :disabled="isLoading">
                    {{ isLoading ? 'ログイン中...' : 'ログイン' }}
                </button>
            </form>

            <div class="debug-section" style="margin-top: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <p style="font-size: 14px; color: #666;">開発用テストログイン:</p>
                <button 
                    @click="handleTestLogin" 
                    class="btn-secondary" 
                    style="background: #28a745; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;"
                >
                    テストユーザーでログイン
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

const { login, checkAuthStatus, isLoggedIn } = useAuth()


// フォームデータ
const form = ref({
    email: '',
    password: ''
})

const isLoading = ref(false)

onMounted(() => {
    checkAuthStatus()
    if (isLoggedIn.value) {
        navigateTo('/')
    }
})


// ログイン処理
const handleLogin = async () => {
    try {
        // バリデーション
        if (!form.value.email || !form.value.password) {
            alert('メールアドレスとパスワードを入力してください')
            return
        }

        isLoading.value = true


        // TODO: Laravel APIにログインリクエストを送信
        console.log('ログイン試行:', form.value)

        // 仮の処理（後でAPI連携に置き換え）
        // 簡単なバリデーション（デモ用）
        if (form.value.email === 'test@example.com' && form.value.password === 'password') {
            // ログイン成功の処理
            const userData = {
                id: 1,
                name: 'Test User',
                email: form.value.email
            }

            login(userData, 'dummy_token_12345')

            alert('ログインしました！')

            // ホーム画面にリダイレクト
            await navigateTo('/')

        } else {
            alert('メールアドレスまたはパスワードが間違っています\n（テスト用: test@example.com / password）')
        }

    } catch (error) {
        console.error('ログインエラー:', error)
        alert('ログインに失敗しました')
    } finally {
        isLoading.value = false
    }
}

// テスト用ログイン（開発中のみ）
const handleTestLogin = async () => {
    try {
        const userData = {
            id: 1,
            name: 'Test User',
            email: 'test@example.com'
        }

        login(userData, 'dummy_token_12345')

        alert('テストログインしました！')
        await navigateTo('/')

    } catch (error) {
        console.error('テストログインエラー:', error)
        alert('テストログインに失敗しました')
    }
}

// SEO設定
useHead({
    title: 'ログイン - SHARE',
    meta: [
        { name: 'description', content: 'SHAREにログインして投稿を共有しましょう' }
    ]
})
</script>

<style scoped>
.container {
    min-height: 100vh;
    background-color: #000000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.header {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
}

.logo {
    height: 2.5rem;
    width: auto;
}

.nav {
    display: flex;
    gap: 1rem;
}

.nav-link {
    color: white;
    text-decoration: none;
    transition: color 0.3s;
}

.nav-link:hover {
    color: #d1d5db;
}

.form-container {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    padding: 1.5rem;
    width: 100%;
    max-width: 28rem;
}

.form-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    text-align: center;
    margin: 0 0 1.5rem 0;
}

.form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #555;
    border-radius: 0.5rem;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.3s, box-shadow 0.3s;
    box-sizing: border-box;
}

.input:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.btn-primary {
    width: 30%;
    background-color: #6123f1;
    color: white;
    padding: 8px;
    border: 2px solid #1f2937;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
    margin: 0 auto;
}

.btn-primary:hover {
    background-color: #7c3dea;
}
</style>