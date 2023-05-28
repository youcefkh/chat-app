<template>
    <v-app>
        <v-main>
            <v-container fluid>
                <v-layout class="flex-column">
                    <v-sheet xs12 sm8 md4 class="d-flex align-center justify-center w-50 m-auto py-10">
                        <v-card class="elevation-12 w-100">
                            <v-progress-linear
                                v-if="loading"
                                color="primary"
                                indeterminate
                            ></v-progress-linear>
                            <v-toolbar dark color="success">
                                <v-toolbar-title>Register form</v-toolbar-title>
                            </v-toolbar>
                            <v-card-text>
                                <form ref="form" @submit.prevent="register">
                                    <v-text-field
                                        v-model="name"
                                        name="name"
                                        label="Name"
                                        type="name"
                                        placeholder="name"
                                        :error-messages="validationErrors.name"
                                        required
                                    ></v-text-field>

                                    <v-text-field
                                        v-model="email"
                                        name="email"
                                        label="Email"
                                        type="email"
                                        placeholder="email"
                                        :error-messages="validationErrors.email"
                                        required
                                    ></v-text-field>

                                    <v-text-field
                                        v-model="password"
                                        :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'"
                                        name="password"
                                        label="Password"
                                        :type="show1 ? 'text' : 'password'"
                                        placeholder="password"
                                        :error-messages="validationErrors.password"
                                        required
                                        @click:append="show1 = !show1"
                                    ></v-text-field>

                                    <v-text-field
                                        v-model="password_confirmation"
                                        :append-icon="show2 ? 'mdi-eye' : 'mdi-eye-off'"
                                        name="password_confirmation"
                                        label="Password confirmation"
                                        :type="show2 ? 'text' : 'password'"
                                        placeholder="password confirmation"
                                        required
                                        @click:append="show2 = !show2"
                                    ></v-text-field>
                                    <v-btn
                                        type="submit"
                                        class="mt-4 mr-0 ml-auto d-block"
                                        style="width: 100px"
                                        color="success"
                                        value="log in"
                                        :disabled="loading"
                                        >
                                        <span>register</span>
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
    name: "register",
    data() {
        return {
            name: null,
            email: null,
            password: null,
            password_confirmation: null,
            show1: false,
            show2: false,
            loading: false,
            validationErrors: {}
        };
    },
    methods: {
        register() {
            this.loading = true;
            this.validationErrors = {};
            this.axios.post('api/user', {
                name: this.name,
                email: this.email,
                password: this.password,
                password_confirmation: this.password_confirmation
            })
                .then(res => {
                    store.dispatch('setToken', res.data.token);
                    store.dispatch('setUserData', res.data.token);
                    store.dispatch('initializeEcho');
                    this.$router.push({name: 'dashboard', params: {type: 'group', id: 1}})
                })
                .catch(err => {
                    this.validationErrors = err.response.data.errors
                })
                .then(res => {
                    this.loading = false;
                })
        },
    },
};
</script>

<style></style>
