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
        ></textarea>
        <button class="share-btn" @click="handleShare">シェアする</button>
      </div>
    </div>

    <!-- メインコンテンツ -->
    <div class="main">
      <header class="main-header">
        <h1>ホーム</h1>
      </header>

      <div class="timeline">
        <!-- 投稿例 -->
        <div class="post">
          <div class="post-header">
            <span class="post-user">test</span>
            <div class="post-actions">
              <span class="like-btn" @click="handleLikeClick">
                <img src="/images/heart.png" alt="いいね" class="action-icon" /> 1
              </span>
              <span class="cross-btn" @click="handleCrossClick">
                <img src="/images/cross.png" alt="閉じる" class="action-icon" />
              </span>
              <span class="detail-btn" @click="handleDetailClick">
                <img src="/images/detail.png" alt="詳細" class="action-icon" />
              </span>
            </div>
          </div>
          <p class="post-content">test message</p>
        </div>

        <!-- 他の投稿もここに追加 -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useFirebaseAuth } from '~/composables/useFirebaseAuth'

const { user, isLoggedIn, logout } = useFirebaseAuth()


// 新しい投稿内容
const newPost = ref('')

onMounted(() => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
  }
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


// 投稿をシェアする処理
const handleShare = async () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }

  if (!newPost.value.trim()) {
    alert('投稿内容を入力してください')
    return
  }

  try {
    // TODO: Laravel APIに投稿を送信
    console.log('新しい投稿:', newPost.value)

    // 投稿成功後、テキストエリアをクリア
    newPost.value = ''
    alert('投稿しました！（API実装後に実際の投稿機能が動作します）')

  } catch (error) {
    console.error('投稿エラー:', error)
    alert('投稿に失敗しました')
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

const handleLikeClick = () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }
  console.log('いいね機能（後で実装）')
  alert('いいね機能は後で実装します')
}

const handleCrossClick = () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }
  console.log('投稿を非表示（後で実装）')
  alert('投稿非表示機能は後で実装します')
}

const handleDetailClick = () => {
  if (!isLoggedIn.value) {
    navigateTo('/login')
    return
  }
  // 投稿詳細画面に遷移
  console.log('投稿詳細画面に遷移')
  navigateTo('/post/1') // 仮のID、後で動的に変更
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

/* .nav-item.active {
  background-color: #8b5cf6;
} */

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




.share-btn:hover {
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

.timeline {
  padding: 1rem;
  border-left: 1px solid #ffffff;
  border-bottom: 1px solid #ffffff;
}

.post {
  background-color: transparent;
  border-radius: 0;
  padding: 1rem;
  margin-bottom: 0;
  border: none;
}

.post-header {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
}

.post-user {
  font-weight: 600;
  color: #f3f4f6;
  margin-right: 1rem;
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
}

.logout-btn:hover {
  background-color: #7f1d1d !important;
}
</style>