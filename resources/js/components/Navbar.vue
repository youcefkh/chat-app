<template>
    <nav class="py-4">
        <div class="logo">
            <v-icon icon="mdi-wechat" />
        </div>

        <div class="links my-auto">
            <ul role="tablist" class="side-menu-nav d-flex flex-column justify-content-center nav nav-pills">
                <li v-for="link in links" :key="link.name" class="nav-item" :data-title="link.title">
                    <router-link
                        :to="{
                            name: link.route.name,
                            params: link.route.params,
                            query: link.route.query
                        }"
                        class="nav-link"
                        :class="{'active-link': link.route.query.page == currentPage}"
                    >
                        <v-icon :icon="link.icon" />
                    </router-link>
                </li>
            </ul>
        </div>

        <div class="additional-links mt-auto">
            <ul role="tablist" class="side-menu-nav d-flex flex-column justify-content-center nav nav-pills">
                <li class="nav-item" data-title="Logout" @click="logout">
                    <button
                        class="nav-link"
                    >
                        <v-icon icon="mdi-logout" />
                    </button>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
import axiosClient from "../axios";
import store from "../store";
export default {
    data() {
        return {
            currentPage: "profile",
            links: [
                {
                    title: "My Profile",
                    icon: "mdi-account-circle-outline",
                    route: { name: "dashboard", params: null, query: {page: "profile"} },
                },
                {
                    title: "Chats",
                    icon: "mdi-message-processing-outline",
                    route: { name: "dashboard", params: null, query: {page: "chats"} },
                },
                {
                    title: "Groups",
                    icon: "mdi-account-group-outline",
                    route: { name: "dashboard", params: null, query: {page: "groups"} },
                },
            ],
        };
    },
    
    watch: {
        "$route.query.page": {
            handler(value) {
                if(value) {
                    this.currentPage = value;
                }
            },
            deep: true,
            immediate: true,
        },
    },

    computed: {
        user() {
            return store.state.user.data;
        },
    },
    methods: {
        logout() {
            Echo.leave("notifications");
            axiosClient
                .post("/logout")
                .then((result) => {
                    store.dispatch("resetUser");
                    this.$router.push({ name: "login" });
                })
                .catch((err) => {
                    console.log(err);
                });
        },
    },
};
</script>

<style scoped>
nav {
    height: 100vh;
    width: 75px;
    min-width: 75px;
    background-color: #fff;
    box-shadow: 0 2px 4px rgb(15 34 58 / 12%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    z-index: 999;
}
nav .logo {
    color: #7269ef !important;
    font-size: 24px;
}
.side-menu-nav .nav-item .nav-link {
    border-radius: 8px;
    color: #878a92 !important;
    font-size: 17px;
    height: 56px;
    line-height: 56px;
    margin: 0 auto;
    padding: 0;
    text-align: center;
    width: 56px;
}
.side-menu-nav .nav-item .nav-link.active-link, .side-menu-nav .nav-item .nav-link:hover {
    background-color: #f7f7ff;
    color: #7269ef !important;
}
</style>
