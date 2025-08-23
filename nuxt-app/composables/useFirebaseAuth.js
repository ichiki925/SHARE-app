import { ref, computed, readonly } from 'vue'
import {
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signOut,
  onAuthStateChanged,
  updateProfile
} from 'firebase/auth'


const user = ref(null)
const isLoading = ref(false)

export const useFirebaseAuth = () => {
  const { $auth } = useNuxtApp()  // ← ここがポイント

  const isLoggedIn = computed(() => !!user.value)

  const initAuth = () => 
    new Promise((resolve) => {
      onAuthStateChanged($auth, (firebaseUser) => {
        user.value = firebaseUser
          ? {
              uid: firebaseUser.uid,
              email: firebaseUser.email,
              displayName: firebaseUser.displayName || firebaseUser.email?.split('@')[0],
              _firebaseUser: firebaseUser
          }
          : null
        resolve(firebaseUser)
      })
    })

  const login = async (email, password) => {
    try {
      isLoading.value = true
      const cred = await signInWithEmailAndPassword($auth, email, password) // ← $auth
      const u = cred.user
      user.value = {
        uid: u.uid,
        email: u.email,
        displayName: u.displayName || u.email?.split('@')[0],
        _firebaseUser: u
      }
      return user.value
    } finally {
      isLoading.value = false
    }
  }

  const register = async (email, password, username) => {
    try {
      isLoading.value = true
      const userCredential = await createUserWithEmailAndPassword($auth, email, password)
      const firebaseUser = userCredential.user

      await updateProfile(firebaseUser, {
        displayName: username
      })

      user.value = {
        uid: firebaseUser.uid,
        email: firebaseUser.email,
        displayName: username,
        _firebaseUser: firebaseUser
      }

      return user.value
    } catch (error) {
      console.error('新規登録エラー:', error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    await signOut($auth) // ← $auth
    user.value = null
  }

  const getAuthToken = async () => {
    if (user.value?._firebaseUser) {
      try {
        return await user.value._firebaseUser.getIdToken()
      } catch {
        await logout()
        return null
      }
    }
    return null
  }

  const getErrorMessage = (error) => {
    switch (error.code) {
      case 'auth/user-not-found':
        return 'このメールアドレスは登録されていません'
      case 'auth/wrong-password':
        return 'パスワードが間違っています'
      case 'auth/email-already-in-use':
        return 'このメールアドレスは既に使用されています'
      case 'auth/weak-password':
        return 'パスワードが弱すぎます（6文字以上にしてください）'
      case 'auth/invalid-email':
        return '無効なメールアドレスです'
      case 'auth/too-many-requests':
        return 'ログイン試行回数が多すぎます。しばらく待ってからお試しください'
      case 'auth/network-request-failed':
        return 'ネットワークエラーが発生しました'
      default:
        return error.message || '予期しないエラーが発生しました'
    }
  }

  return {
    user: readonly(user),
    isLoggedIn,
    isLoading: readonly(isLoading),
    initAuth,
    login,
    register,
    logout,
    getAuthToken,
    getErrorMessage
  }
}