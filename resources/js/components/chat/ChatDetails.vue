<template>
    <div class="chat-sidebar p-4">
        <header>
            <div class="text-right text-muted">
                <v-icon icon="mdi-close-box" @click="$emit('hideDetails')" />
            </div>
        </header>

        <div class="d-flex flex-column align-center my-5">
            <div class="img-container mb-5">
                <img
                    :src="conversation.picture"
                    alt=""
                />
            </div>
            <h5 class="name text-truncate font-size-16 mb-2">
                {{ conversation.name }}
            </h5>
            <div class="status">
                <div v-if="isOnline" class="d-flex align-items-center">
                    <v-icon
                        class="text-green-accent-3 mr-2 text-subtitle-2"
                        icon="mdi-record-circle"
                    />
                    <span class="text-muted">Active</span>
                </div>

                <div v-else class="d-flex align-items-center">
                    <v-icon
                        class="text-muted"
                        icon="mdi-account-off-outline"
                    />
                    <span class="text-muted">Absent</span>
                </div>
            </div>
        </div>

        <hr class="my-10 text-gray-400" />

        <div class="page-content px-3 overflow-y-auto">
            <div class="about bg-white p-6 mb-3 rounded">
                <header
                    class="mb-8 d-flex justify-content-between align-items-center"
                >
                    <h5 class="font-size-14 mb-0">
                        <v-icon class="mr-1" icon="mdi-account-details" />
                        <span>About</span>
                    </h5>
                </header>

                <div class="mb-7">
                    <p class="text-muted mb-1">Name</p>
                    <h5 class="font-size-14">{{ conversation.name }}</h5>
                </div>
                <div class="mb-7">
                    <p class="text-muted mb-1">Email</p>
                    <h5 class="font-size-14">{{ conversation.email }}</h5>
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

            <div v-if="convType == 'group'">
                <group-options :group="conversation"/>
            </div>
        </div>
    </div>
</template>

<script>
import store from "../../store";
import moment from "moment";
import axios from "axios";
import GroupOptions from './GroupOptions.vue';
export default {
  components: { GroupOptions },
    props: [
        'conversation',
        'isOnline',
        'convType'
    ],

    data() {
        return {
            moment: moment,
            location: null,
        };
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
.chat-sidebar {
    visibility: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
    width: 380px;
    min-width: 380px;
    background-color: #f5f7fb;
    overflow: hidden;
    border-left: 4px solid #f0eff5;
    position: absolute;
    right: 0;
    top: 0;
    transform: translateX(100%);
    transition: all 1s cubic-bezier(0, 0.68, 0.04, 1.11);
}

.chat-sidebar.active {
    visibility: visible;
    transform: translateX(0px);
}

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
