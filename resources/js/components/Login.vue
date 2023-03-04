<template>
    <v-app>
        <v-main>
            <v-container fluid>
                <v-layout class="flex-column py-10">
                    <v-alert
                        v-if="error"
                        type="warning"
                        class="w-50 m-auto mb-5"
                        >The email or password must be incorrect</v-alert
                    >
                    <v-sheet
                        xs12
                        sm8
                        md4
                        class="d-flex align-center justify-center w-50 m-auto"
                    >
                        <v-card class="elevation-12 w-100">
                            <v-toolbar dark color="primary">
                                <v-toolbar-title>Login form</v-toolbar-title>
                            </v-toolbar>
                            <v-card-text>
                                <form ref="form" @submit.prevent="login">
                                    <v-text-field
                                        v-model="email"
                                        name="email"
                                        label="Email"
                                        type="email"
                                        placeholder="email"
                                        required
                                    ></v-text-field>

                                    <v-text-field
                                        v-model="password"
                                        :append-icon="
                                            show1 ? 'mdi-eye' : 'mdi-eye-off'
                                        "
                                        :type="show1 ? 'text' : 'password'"
                                        name="password"
                                        label="Password"
                                        placeholder="password"
                                        required
                                        @click:append="show1 = !show1"
                                    ></v-text-field>
                                    <v-btn
                                        type="submit"
                                        class="mt-4"
                                        style="width: 100px"
                                        color="primary"
                                        value="log in"
                                        :disabled="loading"
                                    >
                                        <span v-if="!loading">Login</span>
                                        <i
                                            v-else
                                            class="fas fa-spinner fa-pulse"
                                        ></i>
                                    </v-btn>
                                </form>
                            </v-card-text>
                        </v-card>
                    </v-sheet>
                </v-layout>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
import store from '../store';
export default {
    name: "Login",
    data() {
        return {
            email: "",
            password: "",
            loading: false,
            error: false,
            show1: false
        };
    },
    methods: {
        login() {
            this.loading = true
            this.error = false
            this.axios.post('api/login', {
                email: this.email,
                password: this.password
            })
                .then(async (res) => {
                    store.dispatch('setToken', res.data.token);
                    store.dispatch('setUserData', res.data.token);
                    store.dispatch('initializeEcho');
                    this.$router.push({name: 'dashboard', params: {type: 'group', id: 1}})
                })
                .catch(err => {
                    this.error = true
                    console.log(err)
                })
                .then(res => {
                    this.loading = false;
                })
        },
    },
};
</script>

<style></style>
