<template>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="/images/logo.png" alt="SHARE" class="logo" />
        </div>

        <nav class="nav">
            <NuxtLink to="/" class="nav-item">
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
                v-model="localNewPost"
                class="share-textarea"
                placeholder="今何してる？"
                maxlength="120"
                :disabled="isSubmitting"
            ></textarea>
            <button
                class="share-btn"
                @click="handleShare"
                :disabled="isSubmitting || !localNewPost.trim()"
            >
                {{ isSubmitting ? '投稿中...' : 'シェアする' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'


const props = defineProps({
    newPost: {
        type: String,
        default: ''
    },
    isSubmitting: {
        type: Boolean,
        default: false
    },
    onLogout: {
        type: Function,
        required: true
    },
    onShare: {
        type: Function,
        required: true
    }
})

const emit = defineEmits(['update:newPost'])

const localNewPost = ref(props.newPost)

watch(() => props.newPost, (newValue) => {
    localNewPost.value = newValue
})

watch(localNewPost, (newValue) => {
    emit('update:newPost', newValue)
})

const handleLogout = () => {
    props.onLogout()
}

const handleShare = () => {
    props.onShare()
}
</script>

<style scoped>
.sidebar {
    width: 300px;
    background-color: #000000;
    padding: 1.5rem;
    flex-shrink: 0;
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

.share-textarea:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.share-textarea::placeholder {
    color: #9ca3af;
}

.share-btn {
    position: relative;
    display: inline-block;
    padding: 8px 28px;
    color: #fff;
    background: #6d28d9;
    border-radius: 9999px;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s, opacity 0.3s;
}

.share-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.share-btn::before{
    --outline: #d8d8d8;
    --thick:   2px;
    --spread:  0px;

    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    pointer-events: none;

    box-shadow:
        0 calc(-1*var(--thick)) 0 var(--spread) var(--outline),
        calc(-1*var(--thick)) 0 0 var(--spread) var(--outline);
}

.share-btn:hover:not(:disabled) {
    background-color: #7c3aed;
}


.logout-btn:hover {
    background-color: #7f1d1d !important;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        padding: 1rem;
        flex-shrink: 0;
        max-height: 60vh;
        overflow-y: auto;
    }

    .sidebar-header {
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }

    .nav {
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }

    .nav-item {
        padding: 0.5rem;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }

    .nav-icon {
        width: 1.25rem;
        height: 1.25rem;
    }

    .share-section {
        margin-bottom: 1rem;
    }

    .share-title {
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }

    .share-textarea {
        height: 80px;
        margin-bottom: 0.75rem;
        font-size: 16px; 
        padding: 0.5rem;
    }

    .share-btn {
        padding: 6px 20px;
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .sidebar {
        padding: 0.75rem;
        max-height: 50vh;
    }

    .share-textarea {
        height: 60px;
        font-size: 16px;
    }
}
</style>