<template>
    <div class="wrapper">
        <div class="img-container">
            <img :src="image" alt="" />
        </div>
        <div v-if="onlineIcon && isOnline()" class="online-icon"></div>
    </div>
</template>

<script>
export default {
    props: {
        onlineUsers: {
            type: Array,
            default: null
        },
        image: String,
        user_id: {
            type: Number,
            default: null
        },
        onlineIcon: {
            type: Boolean,
            default: true
        }
    },

    methods: {
        isOnline() {
            if(this.onlineUsers == null) return true;

            return this.onlineUsers.some((user) => {
                return user.id === this.user_id;
            });
        },
    }
};
</script>

<style scoped>
.img-container {
    height: 40px;
    width: 40px;
    overflow: hidden;
    border-radius: 50%;
}

.img-container img {
    height: 100%;
    width: 100%;
    object-fit: cover;
}

.wrapper {
    position: relative;
    width: fit-content;
}

.online-icon {
    height: 12px;
    width: 12px;
    position: absolute;
    bottom: 0;
    right: 0;
    border: 2px solid #fff;
    border-radius: 50%;
    transform: translateX(20%);
    background-color: #60d960;
}
</style>
