<template>
    <div class="container">
        <AuthHeader />

        <div class="form-container">
            <h2 class="form-title">ログイン</h2>

            <Form :validation-schema="schema" @submit="onSubmit" v-slot="{ isSubmitting }" class="form">
                <Field
                    name="email"
                    as="input"
                    type="email"
                    placeholder="メールアドレス"
                    class="input"
                />
                <ErrorMessage name="email" class="error-message" />

                <Field
                    name="password"
                    as="input"
                    type="password"
                    placeholder="パスワード"
                    class="input"
                />
                <ErrorMessage name="password" class="error-message" />

                <button type="submit" class="btn-primary" :disabled="isSubmitting || isLoading">
                    {{ (isSubmitting || isLoading) ? 'ログイン中...' : 'ログイン' }}
                </button>
            </Form>

            <div v-if="errorMessage" class="error-message">
                {{ errorMessage }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Form, Field, ErrorMessage } from 'vee-validate'
import * as yup from 'yup'
import { useFirebaseAuth } from '~/composables/useFirebaseAuth'

const {
    login,
    isLoggedIn,
    isLoading,
    getErrorMessage
} = useFirebaseAuth()

const schema = yup.object({
    email: yup.string()
        .required('メールアドレスを入力してください')
        .email('正しいメール形式で入力してください'),
    password: yup.string()
        .required('パスワードを入力してください'),
})

const errorMessage = ref('') // 想定外エラーの表示用（任意）

const onSubmit = async (values, { setFieldError }) => {
    try {
        errorMessage.value = ''
        await login(values.email.trim(), values.password)
        await navigateTo('/')
    } catch (e) {
        // Firebaseのエラーをフィールドに反映（＋全体にも表示したい場合は下行を残す）
        const msg = getErrorMessage(e)
        setFieldError('email', msg)
        errorMessage.value = msg // 任意
    }
}

onMounted(() => {
    if (isLoggedIn.value) {
        navigateTo('/')
    }
})

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