<template>
    <div class="d-flex">
        <navbar />

        <div class="sidebar p-4">
            <profile v-if="currentPage=='profile'" />
            <chats v-if="currentPage=='chats'" :messagesHistory="messagesHistory" @messageRecieved="getMessagesHistory"/>
        </div>

        <chat class="flex-grow-1" :chat="chat" @messageSent='getMessagesHistory' @seeMessages="resetNotificationsCounter"/>
    </div>
</template>

<script>
import Navbar from "./Navbar.vue";
import Profile from "./chat/Profile.vue";
import Chats from "./chat/Chats.vue";
import Chat from '../components/Chat.vue';
import axiosClient from '../axios';
import store from '../store';
export default {
    data() {
        return {
            currentPage: "profile",
            chat: {
                type: "group",
                id: 1
            },
            messagesHistory: [],
        };
    },

    components: {
        Navbar,
        Profile,
        Chats,
        Chat
    },

    computed: {
        user() {
            return store.state.user.data;
        },
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
        "$route.params": {
            handler(params) {
                this.chat = {type: params.type, id: params.id}
            },
            deep: true,
            immediate: true,
        },
    },

    methods: {
        async getMessagesHistory() {
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
                        return {
                            id: obj.id,
                            message: obj.message,
                            type: obj.type,
                            created_at: obj.created_at,
                            name: user_name,
                            user_id: user_id,
                            thumbnail: thumbnail,
                            notification_count: obj.unseen_messages,
                            conv_type: "private",
                            conv_id: user_id,
                        };
                    }
                });
            } catch (error) {
                console.log(error);
            }
        },

        resetNotificationsCounter(conversation) {
            this.messagesHistory = this.messagesHistory.map(obj => {
                if(obj.conv_type == conversation.type && obj.conv_id == conversation.id) {
                    return {
                        ...obj,
                        notification_count: 0
                    }
                }else {
                    return {...obj}
                }
            });

            console.log(this.messagesHistory);
        }
    },

    mounted() {
        this.getMessagesHistory();
    }
};
</script>

<style scoped>
.sidebar {
    height: 100vh;
    width: 380px;
    background-color: #f5f7fb;
    overflow: hidden;
}
</style>
