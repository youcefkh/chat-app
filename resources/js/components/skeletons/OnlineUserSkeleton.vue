<template>
    <div class="user-skeleton">
        <div class="user-skeleton-block py-4 px-2" v-for="n in 4" :key="n">
            <div class="user-skeleton-avatar"></div>
            <div class="user-skeleton-content">
                <div class="user-skeleton-line"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    methods: {
        randomInt(min, max) {
            return Math.floor(Math.random() * (max - min + 1) + min);
        },
    },
};
</script>

<style scoped>
.user-skeleton {
    width: 100%;
    height: 72px;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
}
.user-skeleton-block {
    min-width: 80px;
    height: 50px;
    text-align: center;
    scroll-snap-align: start;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #e6ebf5;
    margin-right: 1rem;
    border-radius: 10%;
    position: relative;
}

.user-skeleton-block:before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.2) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    animation: user-skeleton-animation 1.5s ease-in-out infinite;
}

@keyframes user-skeleton-animation {
    from {
        transform: translateX(-100%);
    }
    to {
        transform: translateX(100%);
    }
}

.user-skeleton-avatar {
    position: absolute !important;
    top: 0;
    transform: translateY(-50%);
    background-color: #e6ebf5;
    height: 40px;
    width: 40px;
    border-radius: 50%;
    border: 2px solid #fff1f1;
}

/* .user-skeleton-content {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.user-skeleton-line {
    background-color: #e6ebf5;
    border-radius: 5px;
    height: 25px;
    margin-bottom: 5px;
} */
</style>
