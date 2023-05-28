<template>
    <div>
        <header v-if="!isLoggedIn" class="px-5 py-2 bg-blue-lighten-5">
            <nav
                class="d-flex flex-row justify-content-between align-items-center"
            >
                <div class="logo">
                    <v-icon icon="mdi-wechat" />
                    <p class="text-h5 d-inline-block m-0">ChatOn</p>
                </div>
                <div>
                    <v-btn class="mr-5" color="primary">
                        <router-link class="text-white" :to="{ name: 'login' }">Login</router-link>
                    </v-btn>
                    <v-btn color="success">
                        <router-link class="text-white" :to="{ name: 'register' }"
                            >Register</router-link
                        >
                    </v-btn>
                </div>
            </nav>
        </header>

        <div class="main">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import store from "./store";
export default {
    computed: {
        isLoggedIn() {
            return store.state.user.token ? true : false;
        },
        notification() {
            return store.state.notifications;
        },
    },
    watch: {
        notification(n, o) {
            setTimeout(function () {
                store.state.notifications = {};
            }, 3000);
        },
    },
};
</script>

<style scoped>
nav .logo {
    color: #7269ef !important;
    font-size: 24px;
}
</style>
