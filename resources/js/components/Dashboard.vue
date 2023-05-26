<template>
    <div class="d-flex main-container">
        <navbar class="nav" />

        <div class="sidebar p-4">
            <profile v-if="currentPage == 'profile'" />
            <chats
                v-if="currentPage == 'chats'"
                :conversations="{ messagesHistory, isLoading }"
                :onlineUsers="onlineUsers"
                @messageRecieved="getMessagesHistory"
                @search="filterMessagesHistory"
            />
            <groups v-if="currentPage == 'groups'" />
            <contacts v-if="currentPage == 'contacts'" />
        </div>

        <chat
            class="chat flex-grow-1"
            :class="{'active': isShowChat}"
            :chat="chat"
            @messageSent="getMessagesHistory"
            @seeMessages="resetNotificationsCounter"
            @hideChat="hideChat"
            :onlineUsers="onlineUsers"
        />
    </div>
</template>

<script>
import Navbar from "./Navbar.vue";
import Profile from "./chat/Profile.vue";
import Chats from "./chat/Chats.vue";
import groups from "./chat/Groups.vue";
import Chat from "../components/Chat.vue";
import axiosClient from "../axios";
import store from "../store";
import Contacts from "./chat/Contacts.vue";
export default {
    data() {
        return {
            currentPage: "profile",
            chat: {
                type: "group",
                id: 1,
            },
            messagesHistory: [],
            messagesHistoryBis: [],
            onlineUsers: [],
            isLoading: false,
        };
    },

    components: {
        Navbar,
        Profile,
        Chats,
        Chat,
        groups,
        Contacts,
    },

    computed: {
        user() {
            return store.state.user.data;
        },

        isMobile() {
            return store.state.isMobile;
        },

        isShowChat() {
            return store.state.isShowChat;
        },
    },

    watch: {
        "$route.query.page": {
            handler(value) {
                if (value) {
                    this.currentPage = value;
                }
            },
            deep: true,
            immediate: true,
        },
        "$route.params": {
            handler(params, oldParms) {
                this.chat = { type: params.type, id: params.id };
                /* const chat = document.getElementById('chatContainer')
                if(JSON.stringify(params) === JSON.stringify(oldParms)){
                    chat ? chat.classList.remove('active') : null;
                }else {
                    chat ? chat.classList.add('active') : null;
                } */
            },
            deep: true,
            immediate: true,
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

        async getMessagesHistory(loading = false) {
            this.isLoading = loading;
            try {
                const result = await axiosClient.get("/chat/messages-history");
                const data = Object.values(result.data);
                this.messagesHistory = data.map((obj) => {
                    if (obj.group_name) {
                        return {
                            id: obj.id,
                            message: obj.sender_name + ": " + obj.message,
                            type: obj.type,
                            created_at: obj.created_at,
                            name: obj.group_name,
                            user_id: null,
                            thumbnail: obj.group_pic,
                            notification_count: obj.unseen_messages,
                            conv_type: "group",
                            conv_id: obj.group_id,
                        };
                    } else {
                        let user_id, user_name, thumbnail;

                        if (this.user.id == obj.u1_id) {
                            user_id = obj.u2_id;
                            user_name = obj.u2_name;
                            thumbnail = obj.u2_pic;
                        } else {
                            user_id = obj.u1_id;
                            user_name = obj.u1_name;
                            thumbnail = obj.u1_pic;
                        }

                        let notification_count = this.user.id == user_id ? 0 : obj.unseen_messages; //keep notifications to 0 when messaging myself
                        return {
                            id: obj.id,
                            message: obj.message,
                            type: obj.type,
                            created_at: obj.created_at,
                            name: user_name,
                            user_id: user_id,
                            thumbnail: thumbnail,
                            notification_count: notification_count,
                            conv_type: "private",
                            conv_id: user_id,
                        };
                    }
                });
                this.messagesHistoryBis = this.messagesHistory;
            } catch (error) {
                console.log(error);
            }
            this.isLoading = false;
        },

        resetNotificationsCounter(conversation) {
            this.messagesHistory = this.messagesHistory.map((obj) => {
                if (
                    obj.conv_type == conversation.type &&
                    obj.conv_id == conversation.id
                ) {
                    return {
                        ...obj,
                        notification_count: 0,
                    };
                } else {
                    return { ...obj };
                }
            });
        },

        filterMessagesHistory(value) {
            this.messagesHistory = this.messagesHistoryBis.filter(
                ({ name }) => {
                    return name.toLowerCase().indexOf(value) > -1;
                }
            );
        },

        checkWindowSize (e) {
            if(window.innerWidth > 900) {
                store.commit('setIsMobile', false);
            }else {
                store.commit('setIsMobile', true);
            }
        },

        hideChat() {
            document.getElementById('chatContainer').classList.remove('active')
        }
    },

    mounted() {
        this.checkWindowSize();
        this.getMessagesHistory(true);
        this.getOnlineUsers();
        window.addEventListener("resize", this.checkWindowSize);
    },

    unmounted() {
        window.removeEventListener("resize", this.checkWindowSize);
    },
};
</script>

<style scoped>
.sidebar {
    height: 100vh;
    width: 380px;
    min-width: 380px;
    background-color: #f5f7fb;
    overflow: hidden;
}

@media only screen and (max-width: 1200px) {
    /*Big smartphones [426px -> 600px]*/
    .main-container {
        display: grid !important;
        grid-template-columns: max-content 1fr;
        grid-template-rows: repeat(2, 1fr);
        grid-column-gap: 0px;
        grid-row-gap: 0px;
        max-height: 100vh;
    }

    .sidebar {
        grid-area: 1 / 1 / 2 / 2;
        height: calc(100vh - 66px) !important;
    }
    .chat {
        grid-area: 1 / 2 / 2 / 3;
        height: calc(100vh - 66px) !important;
    }
    .nav {
        grid-area: 2 / 1 / 3 / 3;
        height: fit-content;
        max-width: 100vw;
    }
}

@media only screen and (max-width: 900px) {
    .sidebar {
        width: 100vw !important;
    }
    .chat {
        width: 0 !important;
        overflow: hidden;
    }

    .chat.active {
        width: 100% !important;
    }

    .sidebar:has(+ .chat.active) {
        width: 0 !important;
        min-width: unset !important;
        padding: 0 !important;
    }
}
</style>
