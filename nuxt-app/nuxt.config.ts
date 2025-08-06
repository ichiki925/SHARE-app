export default defineNuxtConfig({
  devtools: { enabled: true },
  
  modules: [
    '@nuxtjs/tailwindcss'
  ],

  // Docker用の設定
  nitro: {
    host: '0.0.0.0',
    port: 3000
  },

  // Laravel APIとの連携設定
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL || 'http://localhost/api'
    }
  }
})