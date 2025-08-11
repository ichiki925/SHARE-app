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

        <!-- 新規登録フォーム -->
        <div class="form-container">
            <h2 class="form-title">新規登録</h2>
        
            <form @submit.prevent="handleRegister" class="form">
                <!-- ユーザーネーム -->
                <input
                    v-model="form.username"
                    type="text"
                    placeholder="ユーザーネーム"
                    class="input"
                    maxlength="20"
                    required
                />

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
                    minlength="6"
                    required
                />

                <!-- 新規登録ボタン -->
                <button type="submit" class="btn-primary" :disabled="isLoading">
                    {{ isLoading ? '登録中...' : '新規登録' }}
                </button>
            </form>

            <!-- デバッグ用：テスト用新規登録ボタン -->
            <div class="debug-section" style="margin-top: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <p style="font-size: 14px; color: #666;">開発用テスト新規登録:</p>
                <button 
                    @click="handleTestRegister" 
                    class="btn-secondary" 
                    style="background: #ff9800; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;"
                >
                    テストユーザーで新規登録
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
    username: '',
    email: '',
    password: ''
})

const isLoading = ref(false)

// 既にログイン済みの場合はホームにリダイレクト
onMounted(() => {
    checkAuthStatus()
    if (isLoggedIn.value) {
        navigateTo('/')
    }
})

// 新規登録処理
const handleRegister = async () => {
    try {
        // バリデーション
        if (!form.value.username || !form.value.email || !form.value.password) {
            alert('すべての項目を入力してください')
            return
        }

        if (form.value.username.length > 20) {
            alert('ユーザーネームは20文字以内で入力してください')
            return
        }

        if (form.value.password.length < 6) {
            alert('パスワードは6文字以上で入力してください')
            return
        }

        isLoading.value = true


        // TODO: Laravel APIに新規登録リクエストを送信
        console.log('新規登録試行:', form.value)

        // 仮の処理（後でAPI連携に置き換え）
        // 簡単な新規登録成功シミュレーション
        if (form.value.email === 'newuser@example.com') {
            // 新規登録成功の処理
            const userData = {
                id: 2,
                name: form.value.username,
                email: form.value.email
            }

            login(userData, 'dummy_token_67890')

            alert('新規登録とログインが完了しました！')

            // ホーム画面にリダイレクト
            await navigateTo('/')

        } else {
            alert('新規登録機能は後で実装します\n（テスト用: newuser@example.com で登録可能）')
        }

    } catch (error) {
        console.error('新規登録エラー:', error)
        alert('新規登録に失敗しました')
    } finally {
        isLoading.value = false
    }

}

// テスト用新規登録（開発中のみ）
const handleTestRegister = async () => {
    try {
        const userData = {
            id: 2,
            name: 'New Test User',
            email: 'newuser@example.com'
        }

        login(userData, 'dummy_token_67890')

        alert('テスト新規登録とログインが完了しました！')
        await navigateTo('/')

    } catch (error) {
        console.error('テスト新規登録エラー:', error)
        alert('テスト新規登録に失敗しました')
    }
}

// SEO設定
useHead({
    title: '新規登録 - SHARE',
    meta: [
        { name: 'description', content: 'SHAREに新規登録して投稿を始めましょう' }
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
    background-color: #7c3aed;
}


</style>