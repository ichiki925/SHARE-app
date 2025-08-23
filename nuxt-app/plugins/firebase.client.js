import { initializeApp, getApps, getApp } from 'firebase/app'
import { getAuth } from 'firebase/auth'

export default defineNuxtPlugin(() => {
    const cfg = useRuntimeConfig().public

    const firebaseConfig = {
        apiKey: cfg.NUXT_FIREBASE_API_KEY,
        authDomain: cfg.NUXT_FIREBASE_AUTH_DOMAIN,
        projectId: cfg.NUXT_FIREBASE_PROJECT_ID,
        storageBucket: cfg.NUXT_FIREBASE_STORAGE_BUCKET,
        messagingSenderId: cfg.NUXT_FIREBASE_MESSAGING_SENDER_ID,
        appId: cfg.NUXT_FIREBASE_APP_ID,
    }

    const app = getApps().length ? getApp() : initializeApp(firebaseConfig)
    const auth = getAuth(app)

    return { provide: { auth } }
})
