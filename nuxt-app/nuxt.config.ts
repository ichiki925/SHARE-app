export default defineNuxtConfig({
  devtools: { enabled: true },

  // Docker用の設定
  nitro: {
    host: '0.0.0.0',
    port: 3000
  },

  // Vite開発サーバーの設定
  vite: {
    server: {
      host: '0.0.0.0',
      port: 3000,
      hmr: {
        port: process.env.HMR_PORT || 24678,
        host: '0.0.0.0'
      }
    }
  },

  // Laravel APIとの連携設定
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL || 'http://localhost/api',

      NUXT_FIREBASE_API_KEY: process.env.NUXT_FIREBASE_API_KEY,
      NUXT_FIREBASE_AUTH_DOMAIN: process.env.NUXT_FIREBASE_AUTH_DOMAIN,
      NUXT_FIREBASE_PROJECT_ID: process.env.NUXT_FIREBASE_PROJECT_ID,
      NUXT_FIREBASE_STORAGE_BUCKET: process.env.NUXT_FIREBASE_STORAGE_BUCKET,
      NUXT_FIREBASE_MESSAGING_SENDER_ID: process.env.NUXT_FIREBASE_MESSAGING_SENDER_ID,
      NUXT_FIREBASE_APP_ID: process.env.NUXT_FIREBASE_APP_ID,
    }
  }
})