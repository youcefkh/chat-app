<template>
    <div>
        <div class="sidebar p-2">
            <profile v-if="currentPage=='profile'" />
        </div>
    </div>
</template>

<script>
import Profile from "./chat/Profile.vue";
export default {
    data() {
        return {
            currentPage: "",
        };
    },

    components: {
        Profile,
    },

    watch: {
        "$route.query.page": {
            handler(value) {
                this.currentPage = value;
            },
            deep: true,
            immediate: true,
        },
    },
};
</script>

<style scope>
.sidebar {
    height: 100vh;
    width: 380px;
    background-color: #f5f7fb;
    overflow: hidden;
}
</style>
