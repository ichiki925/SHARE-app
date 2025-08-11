<template>
  <div>
    <!-- 認証初期化中のローディング -->
    <div v-if="isAuthInitializing" class="auth-loading">
      <div class="loading-spinner"></div>
      <p>認証情報を確認中...</p>
    </div>

    <!-- アプリのメインコンテンツ -->
    <NuxtPage v-else />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFirebaseAuth } from '~/composables/useFirebaseAuth'

const { initAuth } = useFirebaseAuth()
const isAuthInitializing = ref(true)

onMounted(async () => {
  try {
    await initAuth()
  } catch (error) {
    console.error('認証初期化エラー:', error)
  } finally {
    isAuthInitializing.value = false
  }
})
</script>

<style>
.auth-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background-color: #1a1a2e;
  color: white;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #333;
  border-top: 4px solid #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>