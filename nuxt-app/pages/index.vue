<template>
  <div class="container">
    <Sidebar
      v-model:newPost="newPost"
      :isSubmitting="isSubmitting"
      :onLogout="handleLogout"
      :onShare="handleShare"
    />
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
          <Message
            v-for="post in posts"
            :key="post.id"
            :post="post"
            :showDetailButton="true"
            :onLike="handleLike"
            :onDelete="handleDeleteClick"
            :onDetail="handleDetailClick"
          />

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

const API_BASE_URL = 'http://localhost'

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
      posts.value.unshift(response.data)

      successMessage.value = '投稿しました！'
      newPost.value = ''

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

    const token = await getAuthToken()
    if (!token) {
      navigateTo('/login')
      return
    }

    const post = posts.value.find(p => p.id === postId)
    if (!post) return

    const isLiked = post.user_liked

    if (isLiked) {
      post.likes_count = Math.max(0, post.likes_count - 1)
      post.user_liked = false
      successMessage.value = 'いいねを取り消しました'
    } else {
      post.likes_count = post.likes_count + 1
      post.user_liked = true
      successMessage.value = 'いいねしました！'
    }

    if (isLiked) {
      await $fetch(`${API_BASE_URL}/api/posts/${postId}/like`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
    } else {
      await $fetch(`${API_BASE_URL}/api/posts/${postId}/like`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      })
    }

    setTimeout(() => {
      successMessage.value = ''
    }, 2000)

  } catch (err) {

    await fetchPosts()

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

.no-posts {
  text-align: center;
  padding: 3rem;
  color: #9ca3af;
  font-style: italic;
}

@media (max-width: 768px) {
  .container {
    flex-direction: column;
    height: 100vh;
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

@media (max-width: 480px) {
  .timeline {
    padding: 0.5rem;
  }
}
</style>

