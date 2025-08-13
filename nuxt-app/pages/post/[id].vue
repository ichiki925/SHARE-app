<template>
  <div class="container">
    <!-- サイドバー -->
    <div class="sidebar">
      <div class="sidebar-header">
        <img src="/images/logo.png" alt="SHARE" class="logo" />
      </div>

      <nav class="nav">
        <NuxtLink to="/" class="nav-item">
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
        <h1>コメント</h1>
      </header>

      <!-- エラーメッセージ -->
      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <!-- 成功メッセージ -->
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <!-- ローディング -->
      <div v-if="isLoading" class="loading">
        投稿を読み込み中...
      </div>

      <div v-else-if="post" class="detail-content">


        <!-- 元の投稿 -->
        <div class="original-post">
          <div class="post-header">
            <span class="post-user">{{ post.user_name }}</span>
            <div class="post-actions">
              <span class="like-btn" @click="handleLike">
                <img src="/images/heart.png" alt="いいね" class="action-icon" />
                {{ post.likes_count || 0 }}
              </span>
              <span class="cross-btn" @click="handleDeletePost">
                <img src="/images/cross.png" alt="削除" class="action-icon" />
              </span>
            </div>
          </div>
          <p class="post-content">{{ post.content }}</p>
        </div>

        <!-- コメント一覧 -->
        <div class="comments-section">
          <h3 class="comments-title">コメント</h3>

          <div class="comments-list">
            <div v-for="comment in comments" :key="comment.id" class="comment-item">
              <div class="comment-header">
                <span class="comment-user">{{ comment.user_name }}</span>
              </div>
              <p class="comment-content">{{ comment.content }}</p>
            </div>

            <!-- コメントがない場合 -->
            <div v-if="comments.length === 0" class="no-comments">
              まだコメントがありません
            </div>
          </div>
        </div>

        <!-- コメント投稿フォーム -->
        <div class="comment-form">
          <div class="comment-input-container">
            <input
              v-model="newComment"
              type="text"
              class="comment-input"
              placeholder="コメントを入力..."
              maxlength="120"
              @keyup.enter="handleComment"
              :disabled="isCommentSubmitting"
            />
          </div>
          <div class="comment-btn-container">
            <button
              class="comment-btn"
              @click="handleComment"
              :disabled="isCommentSubmitting || !newComment.trim()"
            >
              {{ isCommentSubmitting ? 'コメント中...' : 'コメント' }}
            </button>
          </div>
        </div>
      </div>
      <div v-else-if="!isLoading" class="not-found">
        投稿が見つかりません
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from '#app'
import { useFirebaseAuth } from '~/composables/useFirebaseAuth'


const { user, isLoggedIn, logout } = useFirebaseAuth()
const route = useRoute()


// リアクティブデータ
const newPost = ref('')
const newComment = ref('')
const post = ref(null)
const comments = ref([])
const isLoading = ref(false)
const isSubmitting = ref(false)
const isCommentSubmitting = ref(false)
const error = ref('')
const successMessage = ref('')

const API_BASE_URL = 'http://localhost:8000'


onMounted(async () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }
  await fetchPostDetail()
})

// 投稿詳細を取得
const fetchPostDetail = async () => {
  try {
    isLoading.value = true
    error.value = ''

    const postId = route.params.id

    // Laravel APIから投稿詳細を取得
    const response = await $fetch(`${API_BASE_URL}/api/posts/${postId}`)

    if (response.status === 'success') {
      post.value = response.data
      // コメントも一緒に取得する場合（Laravel側でコメントも返す場合）
      if (response.data.comments) {
        comments.value = response.data.comments
      }
    } else {
      throw new Error('投稿が見つかりません')
    }

  } catch (err) {
    error.value = '投稿の取得に失敗しました: ' + err.message
    console.error('投稿詳細取得エラー:', err)
  } finally {
    isLoading.value = false
  }
}

// いいね処理
const handleLike = async () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  try {
    error.value = ''
    successMessage.value = ''

    // いいね状態をチェック
    const statusResponse = await $fetch(`${API_BASE_URL}/api/posts/${post.value.id}/like/status?user_id=${user.value?.uid}`)
    const isLiked = statusResponse.data.is_liked
    
    if (isLiked) {
      // いいね取り消し
      const response = await $fetch(`${API_BASE_URL}/api/posts/${post.value.id}/like`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: { user_id: user.value?.uid || 'anonymous' }
      })
      
      if (response.status === 'success') {
        // 投稿データを再取得して最新のいいね数を反映
        await fetchPostDetail()
        successMessage.value = 'いいねを取り消しました'
      }
    } else {
      // いいね追加
      const response = await $fetch(`${API_BASE_URL}/api/posts/${post.value.id}/like`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: {
          user_id: user.value?.uid || 'anonymous',
          user_name: user.value?.displayName || user.value?.email || 'ゲスト'
        }
      })
      
      if (response.status === 'success') {
        // 投稿データを再取得して最新のいいね数を反映
        await fetchPostDetail()
        successMessage.value = 'いいねしました！'
      }
    }

    // 成功メッセージを2秒後に消す
    setTimeout(() => {
      successMessage.value = ''
    }, 2000)

  } catch (err) {
    error.value = 'いいねの処理に失敗しました: ' + err.message
    console.error('いいねエラー:', err)
  }
}

const handleDeletePost = async () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  const confirmed = confirm('この投稿を削除しますか？')
  if (!confirmed) return

  try {
    error.value = ''
    successMessage.value = ''

    // Laravel APIで投稿を削除
    const response = await $fetch(`${API_BASE_URL}/api/posts/${post.value.id}`, {
      method: 'DELETE'
    })

    if (response.status === 'success') {
      successMessage.value = '投稿を削除しました'

      // 削除後はホーム画面に戻る
      setTimeout(() => {
        navigateTo('/')
      }, 1500)
    } else {
      throw new Error('投稿の削除に失敗しました')
    }

  } catch (err) {
    error.value = '投稿の削除に失敗しました: ' + err.message
    console.error('投稿削除エラー:', err)
  }
}




// コメント投稿処理
const handleComment = async () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  if (!newComment.value.trim()) {
    error.value = 'コメントを入力してください'
    return
  }

  try {
    isCommentSubmitting.value = true
    error.value = ''
    successMessage.value = ''

    // Laravel APIにコメント投稿
    const commentData = {
      post_id: post.value.id,
      user_id: user.value?.uid || 'anonymous',
      user_name: user.value?.displayName || user.value?.email || 'ゲスト',
      content: newComment.value.trim()
    }

    // コメント投稿APIエンドポイント
    const response = await $fetch(`${API_BASE_URL}/api/posts/${post.value.id}/comments`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: commentData
    })

    if (response.status === 'success') {
      comments.value.unshift(response.data)
      newComment.value = ''
      successMessage.value = 'コメントしました！'

      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    } else {
      throw new Error('コメントの投稿に失敗しました')
    }

  } catch (err) {
    error.value = 'コメント投稿に失敗しました: ' + err.message
    console.error('コメントエラー:', err)
  } finally {
    isCommentSubmitting.value = false
  }
}

// 投稿をシェアする処理
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

      // 成功メッセージを3秒後に消す
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
    } else {
      throw new Error('投稿の作成に失敗しました')
    }

  } catch (err) {
    error.value = '投稿に失敗しました: ' + err.message
    console.error('投稿エラー:', err)
  } finally {
    isSubmitting.value = false
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
    await navigateTo('/login')

  } catch (err) {
    console.error('ログアウトエラー:', err)
  }
}

// SEO設定
useHead({
  title: 'コメント - SHARE',
  meta: [
    { name: 'description', content: '投稿の詳細とコメント' }
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
  display: inline-inline;
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

/* 左と上だけの細い線（影で描く） */
.share-btn::before{
  --outline: #d8d8d8;   /* ← 線の色（ここだけ変えればOK） */
  --thick:   2px;       /* ← 線の太さ（1～3pxあたり） */
  --spread:  0px;       /* ← 外側への張り出し。1px増やすと外に出る */

  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  pointer-events: none;

  /* [x-offset y-offset blur spread color] を2本 */
  box-shadow:
    0 calc(-1*var(--thick)) 0 var(--spread) var(--outline),  /* 上の線 */
    calc(-1*var(--thick)) 0 0 var(--spread) var(--outline);  /* 左の線 */
}


.share-btn:hover:not(:disabled) {
  background-color: #7c3aed;
}

.main {
  flex: 1;
  background-color: #000000;
  display: flex;
  flex-direction: column;
}

.main-header {
    padding: 1.5rem;
    border-left: 1px solid #ffffff;
    border-bottom: 1px solid #ffffff;
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
  border-left: 1px solid #ffffff;
}

.not-found {
  text-align: center;
  padding: 3rem;
  color: #9ca3af;
  font-style: italic;
  border-left: 1px solid #ffffff;
}


.original-post {
    background-color: transparent;
    border-left: 1px solid #ffffff;
    border-bottom: 1px solid #ffffff;
    padding: 1rem;
}

.post-header {
  display: flex;
  align-items: center;
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

.comments-section {
    margin-bottom: 1rem;
}

.comments-title {
    font-size: 1.125rem;
    color: white;
    padding: 0.7rem;
    border-left: 1px solid #ffffff;
    border-bottom: 1px solid #ffffff;
    text-align: center;
    margin: 0;
}

.comments-list {
  max-height: 400px;
  overflow-y: auto;
}

.comment-item {
    padding: 1rem;
    border-left: 1px solid #ffffff;
    border-bottom: 1px solid #ffffff;
}

.comment-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  /* margin-bottom: 0.5rem; */
}

.comment-user {
  font-weight: 600;
  color: #f3f4f6;
}

.comment-content {
  color: #e5e7eb;
  line-height: 1.5;
  margin: 0;
  word-wrap: break-word;

}

.no-comments {
  padding: 2rem;
  text-align: center;
  color: #9ca3af;
  font-style: italic;
}

.comment-form {
    position: sticky;
    bottom: 0;
    padding: 0.5rem;
}



.comment-input-container {
    margin-bottom: 0.75rem;
}

.comment-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ffffff;
    border-radius: 0.5rem;
    background-color: #000000;
    color: white;
    font-family: inherit;
    box-sizing: border-box;
}

.comment-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.comment-input::placeholder {
    color: #9ca3af;
}

.comment-input:focus {
    outline: none;
    border-color: #6d28d9;
    box-shadow: 0 0 0 1px #6d28d9;
}

.comment-btn-container {
    display: flex;
    justify-content: flex-end;
}

.comment-btn {
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

.comment-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.comment-btn::before{
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  pointer-events: none;
  box-shadow:
    0 -2px 0 0  #d0d0d0,
    -2px 0 0 0 #d0d0d0;
}


.comment-btn:hover:not(:disabled) {
  background-color: #7c3aed;
}





/* .comment-btn:disabled {
  background-color: #6b7280;
  cursor: not-allowed;
} */

.logout-btn:hover {
  background-color: #7f1d1d !important;
}

/* スクロールバーのスタイリング */
.comments-list::-webkit-scrollbar {
  width: 6px;
}

.comments-list::-webkit-scrollbar-track {
  background: #1e293b;
}

.comments-list::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 3px;
}

.comments-list::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}
</style>