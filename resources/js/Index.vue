<template>
    <div>
        <navbar v-if="isLoggedIn" />
        <header v-else class="px-5 py-2 bg-blue-lighten-5">
            <nav
                class="d-flex flex-row justify-content-between align-items-center"
            >
                <h3 class="title">RealTime Apps</h3>
                <div>
                    <v-btn class="mr-5">
                        <router-link :to="{ name: 'login' }">Login</router-link>
                    </v-btn>
                    <v-btn>
                        <router-link :to="{ name: 'register' }"
                            >Register</router-link
                        >
                    </v-btn>
                </div>
            </nav>
        </header>

        <div class="main ml-15 mt-5">
            <v-alert v-if="notification.type" :type="notification.type">{{
                notification.message
            }}</v-alert>
            <router-view class="ml-10"></router-view>
        </div>
    </div>
</template>

<script>
import Navbar from "./components/Navbar.vue";
import store from "./store";
export default {
    components: {
        Navbar,
    },
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

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
a, a:hover {
    text-decoration: none;
    color: #000;
}
</style>
