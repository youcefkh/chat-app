<template>
    <div class="my-profile">
        <header>
            <h4 class="title">My Profile</h4>
        </header>

        <div class="d-flex flex-column align-center my-5">
            <div class="img-container position-relative mb-5">
                <img :src="user.picture" alt="" ref="profilePicture" />
                <div class="layer">
                    <label
                        for="profile-picture"
                        class="d-flex flex-column align-items-center justify-content-center w-100 h-100"
                    >
                        <v-icon icon="mdi-camera" />
                        <span>Update</span>
                    </label>
                    <input
                        type="file"
                        id="profile-picture"
                        accept="image/*"
                        ref="profilePictureInput"
                        hidden
                        @change="updatePicture"
                    />
                </div>
            </div>
            <h5 class="name text-truncate font-size-16 mb-0">
                {{ user.name }}
            </h5>
            <div class="status">
                <v-icon
                    class="text-green-accent-3 mr-2 text-subtitle-2"
                    icon="mdi-record-circle"
                />
                <span class="text-muted">Active</span>
            </div>
        </div>

        <hr class="my-10 text-gray-400" />

        <div class="infos px-3">
            <v-progress-linear
                v-if="isLoading"
                color="primary"
                indeterminate
            ></v-progress-linear>
            <div class="about bg-white p-6 rounded">
                <header
                    class="mb-8 d-flex justify-content-between align-items-center"
                >
                    <h5 class="font-size-14 mb-0">
                        <v-icon class="mr-1" icon="mdi-account-details" />
                        <span>About</span>
                    </h5>

                    <div data-title="Edit">
                        <v-btn
                            variant="outlined"
                            size="x-small"
                            icon
                            color="info"
                            @click="isEdit = true"
                        >
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                    </div>
                </header>

                <div v-if="!isEdit">
                    <div class="mb-7">
                        <p class="text-muted mb-1">Name</p>
                        <h5 class="font-size-14">{{ user.name }}</h5>
                    </div>
                    <div class="mb-7">
                        <p class="text-muted mb-1">Email</p>
                        <h5 class="font-size-14">{{ user.email }}</h5>
                    </div>
                    <div class="mb-7">
                        <p class="text-muted mb-1">Time</p>
                        <h5 class="font-size-14">
                            {{ moment().format("LT") }}
                        </h5>
                    </div>
                    <div class="mb-7">
                        <p class="text-muted mb-1">Location</p>
                        <h5 class="font-size-14">{{ location }}</h5>
                    </div>
                </div>

                <div v-else>
                    <form @submit.prevent="edit">
                        <v-text-field
                            class="mb-5"
                            v-model="profile.name"
                            label="Name"
                            :error-messages="validationErrors.name"
                            required
                        ></v-text-field>

                        <v-text-field
                            class="mb-5"
                            v-model="profile.password"
                            :append-icon="
                                password.show1 ? 'mdi-eye' : 'mdi-eye-off'
                            "
                            name="password"
                            label="Password"
                            :type="password.show1 ? 'text' : 'password'"
                            placeholder="password"
                            :error-messages="validationErrors.password"
                            @click:append="password.show1 = !password.show1"
                        ></v-text-field>

                        <v-text-field
                            class="mb-5"
                            v-model="profile.password_confirmation"
                            :append-icon="
                                password.show2 ? 'mdi-eye' : 'mdi-eye-off'
                            "
                            name="password_confirmation"
                            label="Password confirmation"
                            :type="password.show2 ? 'text' : 'password'"
                            placeholder="password confirmation"
                            @click:append="password.show2 = !password.show2"
                        ></v-text-field>

                        <div class="d-flex justify-content-end gap-2">
                            <v-btn
                                color="primary"
                                variant="text"
                                @click="isEdit = false"
                            >
                                Cancel
                            </v-btn>
                            <v-btn color="primary" variant="flat" type="submit">
                                Edit
                            </v-btn>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import store from "../../store";
import moment from "moment";
import axios from "axios";
import axiosClient from "../../axios";
export default {
    data() {
        return {
            moment: moment,
            location: null,
            isEdit: false,
            profile: {
                name: store.state.user.data.name,
                password: "",
                password_confirmation: "",
            },
            password: {
                show1: false,
                show2: false,
            },
            isLoading: false,
            validationErrors: {},
        };
    },

    computed: {
        user() {
            return store.state.user.data;
        },
    },

    methods: {
        setLocation() {
            this.location = "Tlemcen, Algeria";
        },

        edit() {
            this.isLoading = true;
            this.validationErrors = {};
            axiosClient
                .put(`/user/${this.user.id}`, this.profile)
                .then((res) => {
                    console.log(res);
                    store.dispatch("setUserData");
                })
                .catch((err) => {
                    console.log(err);
                    this.validationErrors = err.response.data.errors;
                })
                .then((res) => {
                    this.isLoading = false;
                    this.isEdit = false;
                });
        },

        async updatePicture() {
            const formData = new FormData();
            const image = this.$refs.profilePictureInput.files[0];

            formData.append('_method', 'put'); //form data dont work with axios.put
            formData.append("picture", image);

            const url = URL.createObjectURL(image);

            try {
                await axiosClient.post(`/user/${this.user.id}`, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });
                this.$refs.profilePicture.src = url;
                store.dispatch("setUserData");
            } catch (error) {
                console.log(error);
            }
        },
    },

    mounted() {
        this.setLocation();
    },
};
</script>

<style scoped>
.img-container {
    height: 100px;
    width: 100px;
    overflow: hidden;
    border: 5px solid #fff;
    border-radius: 50%;
    cursor: pointer;
}

.img-container img {
    height: 100%;
    width: 100%;
    object-fit: cover;
}

.img-container .layer {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    left: 0;
    color: #fff;
    font-size: 0.8rem;
    background: #7269ef7d;
    transform: translateY(100%);
    transition: all 0.2s ease-in-out;
    cursor: pointer;
}

.img-container:hover .layer {
    transform: translateY(0);
}

.img-container .layer label {
    cursor: pointer;
}
</style>
