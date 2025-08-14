import { ref, computed } from 'vue'

const posts = ref([])
const isLoading = ref(false)
const error = ref(null)

export const usePost = () => {

    const apiBase = 'http://localhost/api'

    const fetchPosts = async () => {
        try {
            isLoading.value = true
            error.value = null

            const response = await $fetch(`${apiBase}/posts`, {
                method: 'GET'
            })

            if (response.status === 'success') {
                posts.value = response.data
            } else {
                throw new Error(response.message || '投稿の取得に失敗しました')
            }
        } catch (err) {
            console.error('投稿取得エラー:', err)
            error.value = err.message || '投稿の取得に失敗しました'
        } finally {
            isLoading.value = false
        }
    }

    const createPost = async (postData) => {
        try {
            isLoading.value = true
            error.value = null

            const response = await $fetch(`${apiBase}/posts`, {
                method: 'POST',
                body: postData
            })

            if (response.status === 'success') {
                posts.value.unshift(response.data)
                return response.data
            } else {
                throw new Error(response.message || '投稿の作成に失敗しました')
            }
        } catch (err) {
            console.error('投稿作成エラー:', err)
            error.value = err.message || '投稿の作成に失敗しました'
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const deletePost = async (postId) => {
        try {
            isLoading.value = true
            error.value = null

            const response = await $fetch(`${apiBase}/posts/${postId}`, {
                method: 'DELETE'
            })

            if (response.status === 'success') {
                posts.value = posts.value.filter(post => post.id !== postId)
                return true
            } else {
                throw new Error(response.message || '投稿の削除に失敗しました')
            }
        } catch (err) {
            console.error('投稿削除エラー:', err)
            error.value = err.message || '投稿の削除に失敗しました'
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const likePost = async (postId) => {
        try {
            const response = await $fetch(`${apiBase}/posts/${postId}/like`, {
                method: 'POST'
            })

            if (response.status === 'success') {
                const postIndex = posts.value.findIndex(post => post.id === postId)
                if (postIndex !== -1) {
                posts.value[postIndex] = response.data
                }
                return response.data
            } else {
                throw new Error(response.message || 'いいねに失敗しました')
            }
        } catch (err) {
            console.error('いいねエラー:', err)
            error.value = err.message || 'いいねに失敗しました'
            throw err
        }
    }

    const fetchUserPosts = async (userId) => {
        try {
            isLoading.value = true
            error.value = null

            const response = await $fetch(`${apiBase}/posts/user/${userId}`, {
                method: 'GET'
            })

            if (response.status === 'success') {
                return response.data
            } else {
                throw new Error(response.message || 'ユーザー投稿の取得に失敗しました')
            }
        } catch (err) {
            console.error('ユーザー投稿取得エラー:', err)
            error.value = err.message || 'ユーザー投稿の取得に失敗しました'
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const postsCount = computed(() => posts.value.length)

    const clearError = () => {
        error.value = null
    }

    return {
        posts: readonly(posts),
        isLoading: readonly(isLoading),
        error: readonly(error),
        postsCount,

        fetchPosts,
        createPost,
        deletePost,
        likePost,
        fetchUserPosts,
        clearError
    }
}