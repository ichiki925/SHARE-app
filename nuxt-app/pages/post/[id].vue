<template>
    <div class="container">
        <!-- サイドバー -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="/images/logo.png" alt="SHARE" class="logo" />
            </div>

            <nav class="nav">
                <NuxtLink to="/home" class="nav-item">
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
                ></textarea>
                <button class="share-btn" @click="handleShare">シェアする</button>
            </div>
        </div>

        <!-- メインコンテンツ -->
        <div class="main">
            <header class="main-header">
                <h1>コメント</h1>
            </header>

        <div class="detail-content">
            <!-- 元の投稿 -->
            <div class="original-post">
                <div class="post-header">
                    <span class="post-user">{{ post.username }}</span>
                    <div class="post-actions">
                        <span class="like-btn" @click="handleLike">
                            <img src="/images/heart.png" alt="いいね" class="action-icon" /> 
                            {{ post.likes_count }}
                        </span>
                        <span class="cross-btn" @click="handleClose">
                            <img src="/images/cross.png" alt="閉じる" class="action-icon" />
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
                    <span class="comment-user">{{ comment.username }}</span>
                    <span class="comment-time">{{ formatTime(comment.created_at) }}</span>
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
                maxlength="200"
                @keyup.enter="handleComment"
                />
            </div>
            <div class="comment-btn-container">
                <button class="comment-btn" @click="handleComment">コメント</button>
            </div>
            </div>
        </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// リアクティブデータ
const newPost = ref('')
const newComment = ref('')
const post = ref({
  id: 1,
  username: 'test',
  content: 'test message',
  likes_count: 1,
  created_at: new Date()
})

const comments = ref([
  {
    id: 1,
    username: 'test',
    content: 'test comment',
    created_at: new Date()
  }
])

// 投稿詳細を取得
const fetchPostDetail = async () => {
  try {
    const postId = route.params.id
    // TODO: Laravel APIから投稿詳細とコメントを取得
    console.log('投稿詳細取得:', postId)
    
    // 実際のAPI実装時はここでデータを取得
    // const response = await $fetch(`/api/posts/${postId}`)
    // post.value = response.post
    // comments.value = response.comments
    
  } catch (error) {
    console.error('投稿詳細取得エラー:', error)
  }
}

// いいね処理
const handleLike = async () => {
  try {
    // TODO: Laravel APIにいいねリクエスト
    console.log('いいね:', post.value.id)
    
    // 楽観的更新
    post.value.likes_count += 1
    
  } catch (error) {
    console.error('いいねエラー:', error)
    // エラー時は元に戻す
    post.value.likes_count -= 1
  }
}

// 閉じる処理
const handleClose = () => {
  router.push('/home')
}

// コメント投稿処理
const handleComment = async () => {
  if (!newComment.value.trim()) {
    alert('コメントを入力してください')
    return
  }

  try {
    // TODO: Laravel APIにコメント投稿
    console.log('新しいコメント:', newComment.value)
    
    // 楽観的更新
    const comment = {
      id: Date.now(),
      username: 'current_user', // 実際はログインユーザー名
      content: newComment.value,
      created_at: new Date()
    }
    
    comments.value.unshift(comment)
    newComment.value = ''
    
    alert('コメントしました！（API実装後に実際のコメント機能が動作します）')
    
  } catch (error) {
    console.error('コメントエラー:', error)
    alert('コメント投稿に失敗しました')
  }
}

// 投稿をシェアする処理
const handleShare = async () => {
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
  try {
    // TODO: Laravel APIにログアウトリクエスト
    console.log('ログアウト')
    
    // ログイン画面にリダイレクト
    await navigateTo('/')
    
  } catch (error) {
    console.error('ログアウトエラー:', error)
  }
}

// 時間フォーマット
const formatTime = (date) => {
  const now = new Date()
  const diffInMinutes = Math.floor((now - new Date(date)) / (1000 * 60))
  
  if (diffInMinutes < 1) return 'たった今'
  if (diffInMinutes < 60) return `${diffInMinutes}分前`
  
  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours}時間前`
  
  const diffInDays = Math.floor(diffInHours / 24)
  return `${diffInDays}日前`
}

// コンポーネントマウント時
onMounted(() => {
  fetchPostDetail()
})

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

/* .detail-content {
    padding: 1rem;
    min-height: calc(100vh - 100px);
} */

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
  margin-bottom: 0.5rem;
}

.comment-user {
  font-weight: 600;
  color: #f3f4f6;
}

.comment-time {
  font-size: 0.875rem;
  color: #9ca3af;
}

.comment-content {
  color: #e5e7eb;
  line-height: 1.5;
  margin: 0;
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
}

/* 外側にだけ線を出す影トリック */
.comment-btn::before{
  content: "";
  position: absolute;
  inset: 0;                  /* ボタンと同じ大きさに重ねる */
  border-radius: inherit;
  pointer-events: none;

  /* ←色・太さ・張り出しの調整はここだけ */
  /*   [x-offset y-offset blur spread color] を2本 */
  box-shadow:
    0 -2px 0 0  #d0d0d0,     /* 上の線（yをマイナス）*/
    -2px 0 0 0 #d0d0d0;      /* 左の線（xをマイナス）*/
}


.comment-btn:hover {
  background-color: #7c3aed;
}




.comment-btn:disabled {
  background-color: #6b7280;
  cursor: not-allowed;
}

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