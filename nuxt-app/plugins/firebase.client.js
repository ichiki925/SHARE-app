// plugins/firebase.client.js
import { initializeApp } from 'firebase/app'
import { getAuth } from 'firebase/auth'

const firebaseConfig = {
  apiKey: "AIzaSyCMNvJchJWHjlmS8_Er58fYTIuNTgD0DjE",
  authDomain: "share-app-22a52.firebaseapp.com",
  projectId: "share-app-22a52",
  storageBucket: "share-app-22a52.firebasestorage.app",
  messagingSenderId: "1093720514792",
  appId: "1:1093720514792:web:57bb826ae7e6cb1bb1a95d"
};

// Firebase アプリの初期化
const app = initializeApp(firebaseConfig)

// Firebase Auth の初期化
const auth = getAuth(app)

export { auth }
export default app