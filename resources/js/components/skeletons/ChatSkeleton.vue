<template>
    <div class="chat-skeleton">
        <div
            class="chat-skeleton-message"
            v-for="n in 10"
            :key="n"
            :style="'width:' + randomInt(25, 45) + '%'"
        >
            <div class="chat-skeleton-avatar"></div>
            <div class="chat-skeleton-content">
                <div
                    class="chat-skeleton-line"
                    :style="'height:' + randomInt(50, 150) + 'px'"
                ></div>
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
.chat-skeleton {
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    margin-top: 20px;
}

.chat-skeleton-message {
    position: relative;
    display: flex;
    gap: 10px;
    align-items: flex-end;
    overflow: hidden;
}

.chat-skeleton-message:nth-child(odd) {
    margin-left: auto;
    flex-direction: row-reverse;
}

.chat-skeleton-message:before {
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
    animation: chat-skeleton-animation 1.5s ease-in-out infinite;
}

@keyframes chat-skeleton-animation {
    from {
        transform: translateX(-100%);
    }
    to {
        transform: translateX(100%);
    }
}

.chat-skeleton-avatar {
    min-width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e6ebf5;
}

.chat-skeleton-content {
    display: flex;
    flex-direction: column;
    width: 100%;
}

.chat-skeleton-line {
    background-color: #e6ebf5;
    border-radius: 5px;
}
</style>
