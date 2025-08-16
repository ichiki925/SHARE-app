<template>
    <div class="post">
        <div class="post-header">
            <span class="post-user">{{ post.user_name }}</span>
            <div class="post-actions">
                <span class="like-btn" @click="handleLike">
                    <img src="/images/heart.png" alt="いいね" class="action-icon" />
                    {{ post.likes_count || 0 }}
                </span>
                <span
                    v-if="post.is_owner"
                    class="cross-btn"
                    @click="handleDelete"
                >
                    <img src="/images/cross.png" alt="削除" class="action-icon" />
                </span>
                <span
                    v-if="showDetailButton"
                    class="detail-btn"
                    @click="handleDetail"
                >
                    <img src="/images/detail.png" alt="詳細" class="action-icon" />
                </span>
            </div>
        </div>
        <p class="post-content">{{ post.content }}</p>
    </div>
</template>

<script setup>
// 親から受け取るprops
const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    showDetailButton: {
        type: Boolean,
        default: true
    },
    onLike: {
        type: Function,
        required: true
    },
    onDelete: {
        type: Function,
        required: true
    },
    onDetail: {
        type: Function,
        required: true
    }
})

const handleLike = () => {
    props.onLike(props.post.id)
}

const handleDelete = () => {
    props.onDelete(props.post.id)
}

const handleDetail = () => {
    props.onDetail(props.post.id)
}
</script>

<style scoped>
.post {
    background-color: transparent;
    border-radius: 0;
    padding: 1rem;
    margin-bottom: 1rem;
    border: none;
    border-bottom: 1px solid #333333;
}

.post:last-child {
    border-bottom: none;
}

.post-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.post-user {
    font-weight: 600;
    color: #f3f4f6;
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
    word-wrap: break-word;
    overflow-wrap: break-word;
}

@media (max-width: 768px) {
    .post {
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .post-header {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .post-user {
        font-size: 0.9rem;
    }

    .post-actions {
        gap: 0.5rem;
    }

    .post-actions span {
        padding: 0.125rem 0.25rem;
        font-size: 0.8rem;
    }

    .action-icon {
        width: 1rem;
        height: 1rem;
    }

    .post-content {
        font-size: 0.9rem;
        line-height: 1.4;
    }
}

@media (max-width: 480px) {
    .post {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .post-header {
        margin-bottom: 0.25rem;
    }

    .post-user {
        font-size: 0.85rem;
    }

    .post-actions span {
        padding: 0.1rem 0.2rem;
        font-size: 0.75rem;
        gap: 0.15rem;
    }

    .action-icon {
        width: 0.9rem;
        height: 0.9rem;
    }

    .post-content {
        font-size: 0.85rem;
    }
}
</style>