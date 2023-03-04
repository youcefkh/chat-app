<template>
    <div class="my-profile">
        <header>
            <h4 class="title">My Profile</h4>
        </header>

        <div class="d-flex flex-column align-center my-5">
            <div class="img-container mb-5">
                <img
                    src="https://cdn-icons-png.flaticon.com/512/147/147144.png"
                    alt=""
                />
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
                        >
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                    </div>
                </header>

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
                    <h5 class="font-size-14">{{ moment().format("LT") }}</h5>
                </div>
                <div class="mb-7">
                    <p class="text-muted mb-1">Location</p>
                    <h5 class="font-size-14">{{ location }}</h5>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import store from "../../store";
import moment from "moment";
import axios from "axios";
export default {
    data() {
        return {
            moment: moment,
            location: null,
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
}

.img-container img {
    height: 100%;
    width: 100%;
    object-fit: cover;
}
</style>
