<template>
  <div class="container">
    <!-- サイドバー -->
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

    <!-- メインコンテンツ -->
    <div class="main">
      <header class="main-header">
        <h1>ホーム</h1>
      </header>

      <!-- エラーメッセージ -->
      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <!-- 成功メッセージ -->
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <div class="timeline">
        <!-- ローディング表示 -->
        <div v-if="isLoading" class="loading">
          投稿を読み込み中...
        </div>

        <!-- 投稿一覧 -->
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
                <span class="cross-btn" @click="handleDeleteClick(post.id)">
                  <img src="/images/cross.png" alt="削除" class="action-icon" />
                </span>
                <span class="detail-btn" @click="handleDetailClick(post.id)">
                  <img src="/images/detail.png" alt="詳細" class="action-icon" />
                </span>
              </div>
            </div>
            <p class="post-content">{{ post.content }}</p>
          </div>

          <!-- 投稿がない場合 -->
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

const { user, isLoggedIn, logout } = useFirebaseAuth()

const newPost = ref('')
const posts = ref([])
const isLoading = ref(false)
const isSubmitting = ref(false)
const error = ref('')
const successMessage = ref('')

const API_BASE_URL = 'http://localhost:8000'

// 投稿一覧を取得
const fetchPosts = async () => {
  try {
    isLoading.value = true
    error.value = ''
    
    const response = await $fetch(`${API_BASE_URL}/api/posts`)
    
    if (response.status === 'success') {
      posts.value = response.data.reverse() // 新しい投稿を上に表示
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
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }
  // ページ読み込み時に投稿一覧を取得
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

// 投稿をシェアする処理（Laravel API連携）
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

    // Laravel APIに投稿を送信
    const postData = {
      user_id: user.value?.uid || 'anonymous',
      user_name: user.value?.displayName || user.value?.email || 'ゲスト',
      content: newPost.value.trim()
    }

    const response = await $fetch(`${API_BASE_URL}/api/posts`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: postData
    })

    if (response.status === 'success') {
      successMessage.value = '投稿しました！'
      newPost.value = ''
      
      // 投稿一覧を再取得して最新状態に更新
      await fetchPosts()
      
      // 成功メッセージを3秒後に消す
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    } else {
      throw new Error('投稿の作成に失敗しました')
    }

  } catch (error) {
    error.value = '投稿に失敗しました: ' + error.message
    console.error('投稿エラー:', error)
  } finally {
    isSubmitting.value = false
  }
}

// 削除機能
const handleDeleteClick = async (postId) => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  // 削除確認ダイアログ
  const confirmed = confirm('この投稿を削除しますか？')
  if (!confirmed) return

  try {
    error.value = ''
    successMessage.value = ''

    // Laravel APIで投稿を削除
    const response = await $fetch(`${API_BASE_URL}/api/posts/${postId}`, {
      method: 'DELETE'
    })

    if (response.status === 'success') {
      successMessage.value = '投稿を削除しました'
      
      // 投稿一覧を再取得して最新状態に更新
      await fetchPosts()
      
      // 成功メッセージを3秒後に消す
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    } else {
      throw new Error('投稿の削除に失敗しました')
    }

  } catch (err) {
    error.value = '投稿の削除に失敗しました: ' + err.message
    console.error('投稿削除エラー:', err)
  }
}

// ログアウト処理
const handleLogout = async () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  try {
    console.log('ログアウト')
    await logout()

    // ログイン画面にリダイレクト
    await navigateTo('/login')

  } catch (error) {
    console.error('ログアウトエラー:', error)
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

    // いいね状態をチェック
    const statusResponse = await $fetch(`${API_BASE_URL}/api/posts/${postId}/like/status?user_id=${user.value?.uid}`)
    const isLiked = statusResponse.data.is_liked

    if (isLiked) {
      // いいね取り消し
      await $fetch(`${API_BASE_URL}/api/posts/${postId}/like`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: { user_id: user.value?.uid || 'anonymous' }
      })
      successMessage.value = 'いいねを取り消しました'
    } else {
      // いいね追加
      await $fetch(`${API_BASE_URL}/api/posts/${postId}/like`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: {
          user_id: user.value?.uid || 'anonymous',
          user_name: user.value?.displayName || user.value?.email || 'ゲスト'
        }
      })
      successMessage.value = 'いいねしました！'
    }

    // 投稿一覧を再取得して最新状態に更新
    await fetchPosts()

    // 成功メッセージを2秒後に消す
    setTimeout(() => {
      successMessage.value = ''
    }, 2000)

  } catch (err) {
    error.value = 'いいねの処理に失敗しました: ' + err.message
    console.error('いいねエラー:', err)
  }
}

const handleDetailClick = (postId) => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }
  console.log('投稿詳細画面に遷移', postId)
  navigateTo(`/post/${postId}`)
}

// SEO設定
useHead({
  title: 'ホーム - SHARE',
  meta: [
    { name: 'description', content: 'SHAREのタイムライン' }
  ]
})
</script>

<style scoped>
.container {
  display: flex;
  min-height: 100vh;
  background-color: #000000;
  color: white;
}

.sidebar {
  width: 300px;
  background-color: #000000;
  padding: 1.5rem;
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
}

.main-header {
  padding: 1.5rem;
  border-left: 1px solid #ffffff;
  border-bottom: 1px solid #ffffff;
  background-color: #000000;
}

.main-header h1 {
  font-size: 1.5rem;
  margin: 0;
}

/* メッセージスタイル */
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
  border-left: 1px solid #ffffff;
}

.timeline {
  padding: 1rem;
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
</style>

