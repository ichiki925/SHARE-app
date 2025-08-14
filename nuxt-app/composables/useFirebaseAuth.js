// composables/useFirebaseAuth.js
import { ref, computed, readonly } from 'vue'
import {
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signOut,
  onAuthStateChanged,
  updateProfile
} from 'firebase/auth'
import { auth } from '~/plugins/firebase.client.js'

const user = ref(null)
const isLoading = ref(false)

export const useFirebaseAuth = () => {

  const isLoggedIn = computed(() => !!user.value)

  const initAuth = () => {
    return new Promise((resolve) => {
      onAuthStateChanged(auth, (firebaseUser) => {
        if (firebaseUser) {
          user.value = {
            uid: firebaseUser.uid,
            email: firebaseUser.email,
            displayName: firebaseUser.displayName || firebaseUser.email?.split('@')[0],
            photoURL: firebaseUser.photoURL
          }
        } else {
          user.value = null
        }
        resolve(firebaseUser)
      })
    })
  }

  const login = async (email, password) => {
    try {
      isLoading.value = true
      const userCredential = await signInWithEmailAndPassword(auth, email, password)
      const firebaseUser = userCredential.user

      user.value = {
        uid: firebaseUser.uid,
        email: firebaseUser.email,
        displayName: firebaseUser.displayName || firebaseUser.email?.split('@')[0],
        photoURL: firebaseUser.photoURL
      }

      return user.value
    } catch (error) {
      console.error('ログインエラー:', error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  const register = async (email, password, username) => {
    try {
      isLoading.value = true
      const userCredential = await createUserWithEmailAndPassword(auth, email, password)
      const firebaseUser = userCredential.user

      await updateProfile(firebaseUser, {
        displayName: username
      })

      user.value = {
        uid: firebaseUser.uid,
        email: firebaseUser.email,
        displayName: username,
        photoURL: firebaseUser.photoURL
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
    try {
      isLoading.value = true
      await signOut(auth)
      user.value = null
    } catch (error) {
      console.error('ログアウトエラー:', error)
      throw error
    } finally {
      isLoading.value = false
    }
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
    getErrorMessage
  }
}