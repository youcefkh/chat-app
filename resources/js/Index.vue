<template>
    <div>
        <header v-if="!isLoggedIn" class="px-5 py-2 bg-blue-lighten-5">
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

<style>
</style>
