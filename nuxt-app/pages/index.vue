<template>
  <div class="container">
    <div class="sidebar">
      <div class="sidebar-header">
        <img src="/images/logo.png" alt="SHARE" class="logo" />
      </div>

      <nav class="nav">
        <NuxtLink to="/" class="nav-item active" @click="handleNavClick">
          <img src="/images/home.png" alt="ホーム" class="nav-icon" />
          ホーム
        </NuxtLink>
        <button class="nav-item logout-btn" @click="handleLogout">
          <img src="/images/logout.png" alt="ログアウト" class="nav-icon" />
          ログアウト
        </button>
      </nav>

      <div class="share-section">
        <h3 class="share-title">シェア</h3>
        <textarea
          v-model="newPost"
          class="share-textarea"
          placeholder="今何してる？"
          maxlength="120"
          @click="handleTextareaClick"
          :disabled="isSubmitting"
        ></textarea>
        <button
          class="share-btn"
          @click="handleShare"
          :disabled="isSubmitting || !newPost.trim()"
        >
          {{ isSubmitting ? '投稿中...' : 'シェアする' }}
        </button>
      </div>
    </div>

    <div class="main">
      <header class="main-header">
        <h1>ホーム</h1>
      </header>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <div class="timeline">
        <div v-if="isLoading" class="loading">
          投稿を読み込み中...
        </div>

        <div v-else>
          <div
            v-for="post in posts"
            :key="post.id"
            class="post"
          >
            <div class="post-header">
              <span class="post-user">{{ post.user_name }}</span>
              <div class="post-actions">
                <span class="like-btn" @click="handleLike(post.id)">
                  <img src="/images/heart.png" alt="いいね" class="action-icon" />
                  {{ post.likes_count || 0 }}
                </span>
                <span
                  v-if="post.is_owner"
                  class="cross-btn"
                  @click="handleDeleteClick(post.id)"
                >
                  <img src="/images/cross.png" alt="削除" class="action-icon" />
                </span>
                <span class="detail-btn" @click="handleDetailClick(post.id)">
                  <img src="/images/detail.png" alt="詳細" class="action-icon" />
                </span>
              </div>
            </div>
            <p class="post-content">{{ post.content }}</p>
          </div>

          <div v-if="posts.length === 0" class="no-posts">
            まだ投稿がありません。最初の投稿をしてみましょう！
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFirebaseAuth } from '~/composables/useFirebaseAuth'

const { user, isLoggedIn, logout, getAuthToken } = useFirebaseAuth()

const newPost = ref('')
const posts = ref([])
const isLoading = ref(false)
const isSubmitting = ref(false)
const error = ref('')
const successMessage = ref('')

const API_BASE_URL = 'http://localhost:8000'

const fetchPosts = async () => {
  try {
    isLoading.value = true
    error.value = ''

    const token = await getAuthToken()

    const headers = {
      'Accept': 'application/json'
    }

    if (token && isLoggedIn.value) {
      headers['Authorization'] = `Bearer ${token}`
    }

    const response = await $fetch(`${API_BASE_URL}/api/posts`, {
      headers
    })

    if (response.status === 'success') {
      posts.value = response.data
    } else {
      throw new Error('投稿の取得に失敗しました')
    }
  } catch (err) {
    error.value = '投稿の取得に失敗しました: ' + err.message
    console.error('投稿取得エラー:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await fetchPosts()
})

const handleNavClick = (event) => {
  if (!isLoggedIn.value) {
    event.preventDefault()
    navigateTo('/login')
  }
}

const handleTextareaClick = () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
  }
}

const handleShare = async () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  if (!newPost.value.trim()) {
    error.value = '投稿内容を入力してください'
    return
  }

  try {
    isSubmitting.value = true
    error.value = ''
    successMessage.value = ''

    const token = await getAuthToken()
    if (!token) {
      navigateTo('/login')
      return
    }

    const response = await $fetch(`${API_BASE_URL}/api/posts`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: {
        content: newPost.value.trim()
      }
    })

    if (response.status === 'success') {
      successMessage.value = '投稿しました！'
      newPost.value = ''
      await fetchPosts()

      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    } else {
      throw new Error('投稿の作成に失敗しました')
    }

  } catch (error) {
    if (error.status === 401) {
      await logout()
      navigateTo('/login')
    } else {
      error.value = '投稿に失敗しました: ' + error.message
    }
    console.error('投稿エラー:', error)
  } finally {
    isSubmitting.value = false
  }
}

const handleDeleteClick = async (postId) => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  const confirmed = confirm('この投稿を削除しますか？')
  if (!confirmed) return

  try {
    error.value = ''
    successMessage.value = ''

    const token = await getAuthToken()
    if (!token) {
      navigateTo('/login')
      return
    }

    const response = await $fetch(`${API_BASE_URL}/api/posts/${postId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (response.status === 'success') {
      successMessage.value = '投稿を削除しました'
      await fetchPosts()

      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    } else {
      throw new Error('投稿の削除に失敗しました')
    }

  } catch (err) {
    if (err.status === 401) {
      await logout()
      navigateTo('/login')
    } else if (err.status === 403) {
      error.value = '他人の投稿は削除できません'
    } else {
      error.value = '投稿の削除に失敗しました: ' + err.message
    }
    console.error('投稿削除エラー:', err)
  }
}

const handleLogout = async () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  try {
    await logout()
    await navigateTo('/login')
  } catch (err) {
    console.error('ログアウトエラー:', err)
  }
}

const handleLike = async (postId) => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  try {
    error.value = ''
    successMessage.value = ''

    const token = await getAuthToken()
    if (!token) {
      navigateTo('/login')
      return
    }


    const statusResponse = await $fetch(`${API_BASE_URL}/api/posts/${postId}/like/status`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    const isLiked = statusResponse.data.is_liked

    if (isLiked) {
      await $fetch(`${API_BASE_URL}/api/posts/${postId}/like`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      successMessage.value = 'いいねを取り消しました'
    } else {
      await $fetch(`${API_BASE_URL}/api/posts/${postId}/like`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
      successMessage.value = 'いいねしました！'
    }

    await fetchPosts()

    setTimeout(() => {
      successMessage.value = ''
    }, 2000)

  } catch (err) {
    if (err.status === 401) {
      await logout()
      navigateTo('/login')
    } else {
      error.value = 'いいねの処理に失敗しました: ' + err.message
    }
    console.error('いいねエラー:', err)
  }
}

const handleDetailClick = (postId) => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }
  navigateTo(`/post/${postId}`)
}


useHead({
  title: 'ホーム - SHARE',
  meta: [
    { name: 'description', content: 'SHAREのタイムライン' }
  ]
})
</script>

<style>
/* グローバルスタイル（白い枠を削除） */
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  overflow-x: hidden;
  background-color: #000000;
}

#__nuxt {
  height: 100%;
  background-color: #000000;
}
</style>

<style scoped>
* {
  box-sizing: border-box;
}

.container {
  display: flex;
  background-color: #000000;
  color: white;
  width: 100%;
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}


.sidebar {
  width: 300px;
  background-color: #000000;
  padding: 1.5rem;
  flex-shrink: 0;
}

.sidebar-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
}

.logo {
  height: 2rem;
  width: auto;
}

.nav {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  border-radius: 0.5rem;
  text-decoration: none;
  color: white;
  transition: background-color 0.3s;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  width: 100%;
  text-align: left;
}

.nav-item:hover {
  background-color: #333333;
}

.nav-icon {
  font-size: 1.25rem;
  width: 1.5rem;
  height: 1.5rem;
  object-fit: contain;
}

.share-section {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    border-top: none;
    padding-top: 0;
}

.share-title {
    font-size: 1.125rem;
    margin-bottom: 1rem;
    color: white;
    align-self: flex-start;
}

.share-textarea {
  width: 100%;
  height: 150px;
  padding: 0.75rem;
  border: 1px solid #ffffff;
  border-radius: 0.5rem;
  background-color: #000000;
  color: white;
  resize: vertical;
  font-family: inherit;
  margin-bottom: 1rem;
  box-sizing: border-box;
}

.share-textarea:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.share-textarea::placeholder {
  color: #9ca3af;
}

.share-btn {
  position: relative;
  display: inline-block;
  padding: 8px 28px;
  color: #fff;
  background: #6d28d9;
  border-radius: 9999px;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s, opacity 0.3s;
}

.share-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.share-btn::before{
  --outline: #d8d8d8;
  --thick:   2px;
  --spread:  0px;

  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  pointer-events: none;

  box-shadow:
    0 calc(-1*var(--thick)) 0 var(--spread) var(--outline),
    calc(-1*var(--thick)) 0 0 var(--spread) var(--outline);
}

.share-btn:hover:not(:disabled) {
  background-color: #7c3aed;
}

.main {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.main-header {
  padding: 1.5rem;
  border-left: 1px solid #ffffff;
  border-bottom: 1px solid #ffffff;
  background-color: #000000;
  flex-shrink: 0;
}

.main-header h1 {
  font-size: 1.5rem;
  margin: 0;
}

.error-message {
  background-color: #7f1d1d;
  color: white;
  padding: 1rem;
  margin: 1rem;
  border-radius: 0.5rem;
  border-left: 1px solid #ffffff;
}

.success-message {
  background-color: #065f46;
  color: white;
  padding: 1rem;
  margin: 1rem;
  border-radius: 0.5rem;
  border-left: 1px solid #ffffff;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
}

.timeline {
  padding: 1rem 1rem 0 1rem;
  border-left: 1px solid #ffffff;
  border-bottom: 1px solid #ffffff;
}

.post {
  background-color: transparent;
  border-radius: 0;
  padding: 1rem;
  margin-bottom: 1rem;
  border: none;
  border-bottom: 1px solid #333333;
}

.post:last-child {
  border-bottom: none;
}

.post-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.post-user {
  font-weight: 600;
  color: #f3f4f6;
}

.post-actions {
  display: flex;
  gap: 0.75rem;
}

.post-actions span {
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  transition: background-color 0.3s;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.post-actions span:hover {
  background-color: #333333;
}

.action-icon {
    font-size: 1.25rem;
    width: 1.25rem;
    height: 1.25rem;
    object-fit: contain;
}

.post-content {
  color: #e5e7eb;
  line-height: 1.5;
  margin: 0;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.no-posts {
  text-align: center;
  padding: 3rem;
  color: #9ca3af;
  font-style: italic;
}

.logout-btn:hover {
  background-color: #7f1d1d !important;
}


@media (max-width: 768px) {
  .container {
    flex-direction: column;
    height: 100vh;
  }

  .sidebar {
    width: 100%;
    padding: 1rem;
    flex-shrink: 0;
    max-height: 60vh;
    overflow-y: auto;
  }

  .sidebar-header {
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
  }

  .nav {
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
  }

  .nav-item {
    padding: 0.5rem;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
  }

  .nav-icon {
    width: 1.25rem;
    height: 1.25rem;
  }

  .share-section {
    margin-bottom: 1rem;
  }

  .share-title {
    margin-bottom: 0.5rem;
    font-size: 1rem;
  }

  .share-textarea {
    height: 80px;
    margin-bottom: 0.75rem;
    font-size: 16px; 
    padding: 0.5rem;
  }

  .share-btn {
    padding: 6px 20px;
    font-size: 0.9rem;
  }

  .main {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
  }

  .main-header {
    border-left: none;
    padding: 1rem;
    flex-shrink: 0;
  }

  .main-header h1 {
    font-size: 1.25rem;
  }

  .timeline {
    border-left: none;
    padding: 0.75rem;
    flex: 1;
    overflow-y: auto;
  }

  .post {
    padding: 0.75rem;
    margin-bottom: 0.75rem;
  }

  .post-header {
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .post-user {
    font-size: 0.9rem;
  }

  .post-actions {
    gap: 0.5rem;
  }

  .post-actions span {
    padding: 0.125rem 0.25rem;
    font-size: 0.8rem;
  }

  .action-icon {
    width: 1rem;
    height: 1rem;
  }

  .post-content {
    font-size: 0.9rem;
    line-height: 1.4;
  }

  .error-message,
  .success-message {
    margin: 0.75rem;
    padding: 0.75rem;
    font-size: 0.9rem;
  }

  .loading,
  .no-posts {
    padding: 2rem 1rem;
    font-size: 0.9rem;
  }
}

/* より小さいスマホ画面 */
@media (max-width: 480px) {
  .sidebar {
    padding: 0.75rem;
    max-height: 50vh;
  }

  .share-textarea {
    height: 60px;
    font-size: 16px; /* iOS のズーム防止 */
  }

  .timeline {
    padding: 0.5rem;
  }

  .post {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
  }

  .post-header {
    margin-bottom: 0.25rem;
  }

  .post-user {
    font-size: 0.85rem;
  }

  .post-actions span {
    padding: 0.1rem 0.2rem;
    font-size: 0.75rem;
    gap: 0.15rem;
  }

  .action-icon {
    width: 0.9rem;
    height: 0.9rem;
  }

  .post-content {
    font-size: 0.85rem;
  }
}
</style>

