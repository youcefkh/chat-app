<template>
    <div>
        <h1>Users list</h1>
        <v-list lines="one">
            <router-link
                            v-for="user in users"
                            :key="user.id"
                            :to="{
                                name: 'chat',
                                params: { type: 'private', id: user.id },
                            }"
                        >
                            <v-list-item
                                :title="user.name"
                                :subtitle="user.email"
                                prepend-avatar="https://cdn-icons-png.flaticon.com/512/147/147144.png"
                            ></v-list-item>
                        </router-link>
        </v-list>
        <ul></ul>
    </div>
</template>

<script>
import axiosClient from '../axios';
export default {
    data() {
        return {
            users: null
        }
    },

    methods: {
        getUsers() {
            axiosClient.get('/user')
            .then((result) => {
                this.users = result.data
            }).catch((err) => {
                console.log(err);
            });
        },
        UserEvents() {
            Echo.private('users')
                .listen('UserCreated', (e) => {
                    this.users.push(e.user)
                })
                .listen('UserDeleted', (e) => {
                    this.users = this.users.filter(user => user.id !== e.user.id)
                })
        },
    },

    mounted() {
        this.getUsers();
        this.UserEvents();   
    }
};
</script>

<style></style>
