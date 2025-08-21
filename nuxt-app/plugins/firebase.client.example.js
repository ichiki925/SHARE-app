// Firebase設定のテンプレートファイル
// このファイルをfirebase.client.jsにコピーして実際の値を設定してください

import { initializeApp } from 'firebase/app'
import { getAuth } from 'firebase/auth'

const firebaseConfig = {
    apiKey: "your_api_key_here",
    authDomain: "your_project.firebaseapp.com",
    projectId: "your_project_id",
    storageBucket: "your_project.appspot.com",
    messagingSenderId: "your_sender_id",
    appId: "your_app_id"
};

const app = initializeApp(firebaseConfig)
const auth = getAuth(app)

export { auth }
export default app