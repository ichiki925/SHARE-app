<template>
    <div class="header">
        <img src="/images/logo.png" alt="SHARE" class="logo" />
        <div class="nav">
            <NuxtLink to="/register" class="nav-link">新規登録</NuxtLink>
            <NuxtLink to="/login" class="nav-link">ログイン</NuxtLink>
        </div>
    </div>
</template>

<script setup>
</script>

<style scoped>
.header {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
}

.logo {
    height: 2.5rem;
    width: auto;
}

.nav {
    display: flex;
    gap: 1rem;
}

.nav-link {
    color: white;
    text-decoration: none;
    transition: color 0.3s;
}

.nav-link:hover {
    color: #d1d5db;
}
</style>