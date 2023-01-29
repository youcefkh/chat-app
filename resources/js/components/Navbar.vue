<template>
    <v-layout>
        <v-navigation-drawer expand-on-hover rail>
            <v-list>
                <v-list-item
                    prepend-avatar="https://cdn-icons-png.flaticon.com/512/147/147144.png"
                    :title=user.name
                    :subtitle=user.email
                ></v-list-item>
            </v-list>

            <v-divider></v-divider>

            <v-list density="compact" nav>
                <router-link
                    v-for="item in items"
                    :key="item.title"
                    :to="item.route"
                    @click="logout(item.title)"
                >
                    <v-list-item
                        :prepend-icon="item.icon"
                        :title="item.title"
                        :value="item.title"
                    ></v-list-item>
                </router-link>
            </v-list>
        </v-navigation-drawer>
    </v-layout>
</template>

<script>
import axiosClient from '../axios';
import store from "../store"
export default {
    data() {
        return {
            drawer: true,
            items: [
                { title: "Home", icon: "mdi-home-city", route: "/" },
                { title: "My Account", icon: "mdi-account", route: "/my-account" },
                { title: "Users", icon: "mdi-account-group-outline", route: "/users" },
                { title: "Game", icon: "mdi-ferris-wheel", route: "/game" },
                { title: "Chat", icon: "mdi-chat", route: "/chat/group/1",},
                { title: "Logout", icon: "mdi-logout", route: "" },
            ],
            rail: true,
        };
    },
    computed: {
        user() {
            return store.state.user.data
        }
    },
    methods: {
        logout(item){
            if(item === "Logout"){
                Echo.leave('notifications');
                axiosClient.post('/logout')
                    .then((result) => {
                        store.dispatch('resetUser');
                        this.$router.push({name: "login"});
                    }).catch((err) => {
                        console.log(err);
                    });
            }
        }
    }
};
</script>

<style></style>
