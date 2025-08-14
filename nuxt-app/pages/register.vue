<template>
    <div class="container">

        <div class="header">
            <img src="/images/logo.png" alt="SHARE" class="logo" />
            <div class="nav">
                <NuxtLink to="/register" class="nav-link">新規登録</NuxtLink>
                <NuxtLink to="/login" class="nav-link">ログイン</NuxtLink>
            </div>
        </div>

        <div class="form-container">
            <h2 class="form-title">新規登録</h2>

            <form @submit.prevent="handleRegister" class="form">
                <input
                    v-model="form.username"
                    type="text"
                    placeholder="ユーザーネーム"
                    class="input"
                    maxlength="20"
                    required
                />

                <input
                    v-model="form.email"
                    type="email"
                    placeholder="メールアドレス"
                    class="input"
                    required
                />

                <input
                    v-model="form.password"
                    type="password"
                    placeholder="パスワード (6文字以上)"
                    class="input"
                    minlength="6"
                    required
                />

                <button type="submit" class="btn-primary" :disabled="isLoading">
                    {{ isLoading ? '登録中...' : '新規登録' }}
                </button>
            </form>

            <div v-if="errorMessage" class="error-message">
                {{ errorMessage }}
            </div>

            <div v-if="successMessage" class="success-message">
                {{ successMessage }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFirebaseAuth } from '~/composables/useFirebaseAuth'

const {
    register,
    isLoggedIn,
    isLoading,
    getErrorMessage
} = useFirebaseAuth()

const form = ref({
    username: '',
    email: '',
    password: ''
})

const errorMessage = ref('')
const successMessage = ref('')

onMounted(() => {
    if (isLoggedIn.value) {
        navigateTo('/')
    }
})

const handleRegister = async () => {
    try {
        errorMessage.value = ''
        successMessage.value = ''

        if (!form.value.username.trim() || !form.value.email.trim() || !form.value.password) {
            errorMessage.value = 'すべての項目を入力してください'
            return
        }

        if (form.value.username.length > 20) {
            errorMessage.value = 'ユーザーネームは20文字以内で入力してください'
            return
        }

        if (form.value.password.length < 6) {
            errorMessage.value = 'パスワードは6文字以上で入力してください'
            return
        }

        await register(
            form.value.email.trim(),
            form.value.password,
            form.value.username.trim()
        )

        successMessage.value = '新規登録が完了しました！ホーム画面に移動します...'

        setTimeout(() => {
            navigateTo('/')
        }, 2000)

    } catch (error) {
        console.error('新規登録エラー:', error)
        errorMessage.value = getErrorMessage(error)
    }
}

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

.btn-primary:hover:not(:disabled) {
    background-color: #7c3aed;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.error-message {
    background-color: #fee2e2;
    color: #dc2626;
    padding: 12px;
    border-radius: 8px;
    margin-top: 16px;
    font-size: 14px;
    text-align: center;
    border: 1px solid #fecaca;
}

.success-message {
    background-color: #d1fae5;
    color: #059669;
    padding: 12px;
    border-radius: 8px;
    margin-top: 16px;
    font-size: 14px;
    text-align: center;
    border: 1px solid #a7f3d0;
}

@media (max-width: 480px) {
    .container {
        align-items: flex-start;
        padding-top: 5rem;
    }

    .form-container {
        margin: 0 auto;
    }

    .input {
        font-size: 16px;
    }

    .btn-primary {
        width: 100%;
        max-width: 200px;
    }
}

</style>