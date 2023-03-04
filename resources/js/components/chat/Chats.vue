<template>
    <div class="my-profile">
        <header>
            <h4 class="title">Chats</h4>
        </header>

        <v-text-field
            class="search m-auto my-8"
            placeholder="Search conversations"
            prepend-inner-icon="mdi-magnify"
        ></v-text-field>

        <online-users :onlineUsers="onlineUsers" />

        <h5 class="font-size-15 mb-2 mt-10">Recent</h5>

        <div class="messages-history">
            <div class="chats">
                <div
                    v-for="conv in messagesHistory"
                    :key="conv.id"
                    class="conv-block d-flex flex-row py-4 px-2 mb-1"
                    :class="{
                        active:
                            conv.conv_type == conversation.type &&
                            conv.conv_id == conversation.id,
                    }"
                    @click="openConversation(conv.conv_type, conv.conv_id)"
                >
                    <div class="position-relative" style="min-width: 50px">
                        <div class="wrapper">
                            <div class="img-container">
                                <img :src="conv.thumbnail" alt="" />
                            </div>
                            <div
                                v-if="isOnline(conv.user_id)"
                                class="online-icon"
                            ></div>
                        </div>
                    </div>

                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="text-truncate font-size-15 mb-1">
                            {{ conv.name }}
                        </h5>
                        <p class="chat-user-message text-truncate mb-0">
                            <span v-if="conv.type == 'image'">
                                <v-icon icon="mdi-image" /> Image</span
                            >
                            <span v-else-if="conv.type == 'video'"
                                ><v-icon icon="mdi-file-video" /> Video</span
                            >
                            <span v-else>{{ conv.message }}</span>
                        </p>
                    </div>

                    <div
                        class="font-size-12 d-flex flex-column align-items-end gap-1"
                    >
                        <span style="min-width: max-content">{{
                            formatDate(conv.created_at)
                        }}</span>
                        <span v-if="conv.notification_count > 0">
                            <v-badge
                                color="deep-purple-accent-2"
                                :content="conv.notification_count"
                                inline
                            ></v-badge>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axiosClient from "../../axios";
import store from "../../store";
import OnlineUsers from "./OnlineUsers.vue";
import dateFormatter from "../../mixins/dateFormatter";

export default {
    mixins: [dateFormatter],

    props: {
        messagesHistory: Array,
    },

    data() {
        return {
            onlineUsers: [],
        };
    },

    components: {
        OnlineUsers,
    },

    computed: {
        user() {
            return store.state.user.data;
        },

        conversation() {
            return this.$route.params;
        },
    },

    methods: {
        getOnlineUsers() {
            axiosClient
                .get("/logged-in-users")
                .then((result) => {
                    this.onlineUsers = result.data;
                })
                .catch((err) => {
                    console.log(err);
                });

            Echo.private("notifications").listen("UserSessionChange", (e) => {
                if (e.type === "connected") {
                    this.onlineUsers.push(e.user);
                } else {
                    this.onlineUsers = this.onlineUsers.filter(
                        (user) => user.id !== e.user.id
                    );
                }
            });
        },

        isOnline(user_id) {
            return this.onlineUsers.some((user) => {
                return user.id === user_id;
            });
        },

        openConversation(type, id) {
            const query = this.$route.query;
            this.$router.push({
                name: "dashboard",
                params: { type, id },
                query,
            });
        },

        recieveMessages() {
            Echo.private(`messages.${this.user.id}`).listen("MessageRecieved", (e) => {
                this.$emit('messageRecieved');
            });
        }
    },

    mounted() {
        this.recieveMessages();
        this.getOnlineUsers();
    },
};
</script>

<style scoped>
.search {
    background-color: #e6ebf5;
    color: #7a7f9a;
    border-radius: 10px;
    overflow: hidden;
}

.search .v-input__details {
    display: none;
}

.messages-history {
    max-height: calc(100vh - 330px);
    overflow: auto;
}

.conv-block {
    color: #7a7f9a;
    cursor: pointer;
    transition: all 0.4s;
    border-radius: 5px;
}

.conv-block:hover,
.conv-block.active {
    background-color: #e6ebf5;
}

.img-container {
    height: 40px;
    width: 40px;
    overflow: hidden;
    border-radius: 50%;
}

.img-container img {
    height: 100%;
    width: 100%;
    object-fit: cover;
}

.wrapper {
    position: relative;
    width: fit-content;
}

.online-icon {
    height: 12px;
    width: 12px;
    position: absolute;
    bottom: 0;
    right: 0;
    border: 2px solid #fff;
    border-radius: 50%;
    transform: translateX(20%);
    background-color: #60d960;
}

.chat-user-message {
    font-size: 14px;
}

.messages-history::-webkit-scrollbar-track {
    background-color: #f5f5f5;
}

.messages-history::-webkit-scrollbar {
    width: 10px;
    background-color: #f5f5f5;
}

.messages-history::-webkit-scrollbar-thumb {
    border-radius: 10px;
    background: #7269ef;
}

.loading-progress {
    width: fit-content;
    margin: auto;
}
</style>
