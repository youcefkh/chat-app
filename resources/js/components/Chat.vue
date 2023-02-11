<template>
    <div>
        <!-- waving notification message -->
        <div id="wave-message">
            <span v-html="waveMessage"></span>
            <v-icon class="wave ml-2" icon="mdi-hand-wave" />
        </div>

        <main id="app" class="position-relative">
            <!-- online users -->
            <div class="users-list w-25">
                <div v-if="!friend">
                    <p class="h4">
                        <v-icon icon="mdi-account-badge" />
                        Online Users
                    </p>
                    <v-list class="overflow-hidden" lines="one">
                        <router-link
                            v-for="(user, index) in onlineUsers"
                            :key="index"
                            :to="{
                                name: 'chat',
                                params: { type: 'private', id: user.id },
                            }"
                            class="d-flex mb-2"
                        >
                            <v-list-item
                                :title="user.name"
                                :subtitle="user.email"
                                prepend-avatar="https://cdn-icons-png.flaticon.com/512/147/147144.png"
                            ></v-list-item>
                            <v-icon
                                class="hand"
                                icon="mdi-hand-wave"
                                :title="'wave at ' + user.name"
                                @click="wave($event, user.id)"
                            />
                        </router-link>
                    </v-list>
                </div>
                <div v-else>
                    <p class="h6">This is a private conversation with:</p>
                    <v-list class="d-flex" lines="one">
                        <v-list-item
                            :title="friend.name"
                            :subtitle="friend.email"
                            prepend-avatar="https://cdn-icons-png.flaticon.com/512/147/147144.png"
                        >
                        </v-list-item>
                        <v-icon
                            class="hand"
                            icon="mdi-hand-wave"
                            :title="'wave at ' + friend.name"
                            @click="wave($event, friend.id)"
                        />
                    </v-list>
                </div>
            </div>

            <!-- chat box -->
            <div ref="chatArea" class="chat-area rounded">
                <!-- messages -->
                <div>
                    <div v-for="(message, index) in messages" :key="index">
                        <!-- if message is not of type info -->
                        <div
                            v-if="message.type !== 'info'"
                            class="message"
                            :class="{
                                'message-out': message.author === 'me',
                                'message-in': message.author !== 'me',
                            }"
                        >
                            <span
                                :title="
                                    moment(message.created_at).format('LLL')
                                "
                                class="date"
                                >{{
                                    moment(message.created_at).fromNow()
                                }}</span
                            >

                            <!-- display the message -->
                            <!-- message as an image -->
                            <div
                                class="media-container"
                                v-if="message.type !== 'text'"
                            >
                                <v-img
                                    v-if="message.type == 'image'"
                                    :src="message.body"
                                    :lazy-src="message.body"
                                    aspect-ratio="1"
                                    cover
                                    class="image rounded bg-grey-lighten-2"
                                    @click="openImage(message.body)"
                                >
                                    <template v-slot:placeholder>
                                        <v-row
                                            class="fill-height ma-0"
                                            align="center"
                                            justify="center"
                                        >
                                            <v-progress-circular
                                                indeterminate
                                                color="grey-lighten-5"
                                            ></v-progress-circular>
                                        </v-row>
                                    </template>
                                </v-img>

                                <!-- message as a video -->
                                <video-player
                                    v-else-if="message.type == 'video'"
                                    :src="message.body"
                                    contain
                                    double-click-fullscreen
                                >
                                </video-player>
                            </div>

                            <!-- message as a text -->
                            <p class="message-content mb-0" v-else>
                                {{ message.body }}
                            </p>

                            <!-- author -->
                            <span
                                v-if="message.author !== 'me'"
                                class="message-author"
                            >
                                {{ message.author }}
                            </span>

                            <!-- message status -->
                            <span
                                v-if="message.author === 'me' && message.status"
                                class="message-status"
                            >
                                <v-icon
                                    v-if="message.status == 'pending'"
                                    icon="mdi-clock-outline"
                                />
                                <v-icon
                                    v-else-if="message.status == 'sent'"
                                    icon="mdi-check-all"
                                />
                                <v-icon
                                    v-else-if="message.status == 'failed'"
                                    icon="mdi-alert-circle"
                                />
                            </span>

                            <!-- download file -->
                            <button
                                class="download"
                                v-if="message.type !== 'text'"
                                @click="downloadMedia(message.body)"
                            >
                                <v-icon icon="mdi-download-circle" />
                            </button>
                        </div>

                        <!-- message as info -->
                        <div class="message-info" v-else>
                            <span>{{
                                moment(message.created_at).fromNow()
                            }}</span>
                            <span v-html="message.body"></span>
                        </div>
                    </div>
                </div>

                <!-- see more button -->
                <div ref="seeMoreBtn" class="m-auto" v-if="isDisplaySeeMoreBtn">
                    <v-btn
                        class="mb-5"
                        :prepend-icon="
                            isLoadingMessages
                                ? 'mdi-loading'
                                : 'mdi-plus-box-outline'
                        "
                        @click="getMessages(false)"
                        :disabled="isLoadingMessages"
                    >
                        {{ isLoadingMessages ? "Loading" : "See more" }}
                    </v-btn>
                </div>

                <!-- Message input field -->
                <v-form id="person1-form">
                    <v-text-field
                        v-model="myMessage"
                        id="person1-input"
                        :counter="100"
                        placeholder="Type your message"
                        maxlength="100"
                        @keydown.enter="sendMessage('out')"
                        @blur="setCursorPosition"
                        ref="messageField"
                    ></v-text-field>
                    <div class="options">
                        <EmojiPicker
                            class="emoji"
                            picker-type="input"
                            @select="onSelectEmoji"
                        />

                        <label for="image">
                            <v-icon icon="mdi-camera" />
                        </label>
                        <input
                            hidden
                            type="file"
                            name="image"
                            id="image"
                            accept="image/*, video/*"
                            multiple
                            @change="sendMediaMessage"
                        />
                    </div>
                </v-form>
            </div>

            <!-- chat options -->
            <div class="options w-25 pl-10" v-if="convType == 'group'">
                <p class="h4">
                    <v-icon icon="mdi-account-group-outline" />
                    {{ group ? group.name : null }}
                </p>

                <!-- add new member -->
                <v-dialog v-model="dialog" persistent>
                    <template v-slot:activator="{ props }">
                        <v-btn
                            prepend-icon="mdi-account-plus-outline"
                            variant="outlined"
                            class="mb-3"
                            v-bind="props"
                        >
                            Add new member
                        </v-btn>
                    </template>
                    <v-card>
                        <v-progress-linear v-if="isLoadingDialog" color="primary" indeterminate></v-progress-linear>
                        <v-card-title class="d-flex">
                            <v-icon icon="mdi-account-plus-outline" />
                            <span class="ml-1 text-h5">Add new member</span>
                        </v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12">
                                        <v-text-field
                                            label="Search"
                                            required
                                            @keyup="searchUser"
                                            v-model="memberSearchField"
                                        ></v-text-field>
                                    </v-col>
                                </v-row>
                                <div class="selected-members">
                                    <v-list class="overflow-hidden d-flex">
                                        <v-chip
                                            class="ma-2"
                                            closable
                                            v-for="(
                                                user, index
                                            ) in selectedNewMembers"
                                            :key="index"
                                            @click="removeSelectedMember(user.id)"
                                        >
                                            <v-list-item
                                                class="p-0"
                                                :title="user.name"
                                                prepend-avatar="https://cdn-icons-png.flaticon.com/512/147/147144.png"
                                            ></v-list-item>
                                        </v-chip>
                                    </v-list>
                                </div>

                                <v-list
                                    class="overflow-hidden suggested-members"
                                >
                                    <p class="text-h6">Suggestions</p>
                                    <div v-if="!memberSearchField" class="info">
                                        <span
                                            >Start typing to get
                                            suggestions</span
                                        >
                                    </div>
                                    <div v-else-if="isLoadingSearch" class="info">
                                        <span
                                            >Loading ...</span
                                        >
                                    </div>
                                    <div
                                        v-else-if="
                                            memberSearchField &&
                                            suggestedUsers.length == 0"
                                        class="info"
                                    >
                                        <span>No results found</span>
                                    </div>
                                    <v-row
                                        v-else
                                        v-for="(user, index) in suggestedUsers"
                                        :key="index"
                                        class="m-0 justify-space-between"
                                    >
                                        <v-col cols="8" class="p-0">
                                            <label :for="user.id" class="w-100">
                                                <v-list-item
                                                    :title="user.name"
                                                    :subtitle="user.email"
                                                    prepend-avatar="https://cdn-icons-png.flaticon.com/512/147/147144.png"
                                                ></v-list-item>
                                            </label>
                                        </v-col>
                                        <v-col cols="4" class="p-0">
                                            <v-checkbox
                                                v-model="selectedNewMembers"
                                                :value="user"
                                                :id="user.id + ''"
                                            ></v-checkbox>
                                        </v-col>
                                    </v-row>
                                </v-list>
                            </v-container>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                color="primary"
                                variant="text"
                                @click="discardDialog"
                            >
                                Discard
                            </v-btn>
                            <v-btn
                                color="primary"
                                variant="text"
                                @click="addMember"
                                :disabled = "isLoadingDialog || selectedNewMembers.length == 0"
                            >
                                Add
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

                <!-- show group members -->
                <div class="mb-3">
                    <v-btn
                        prepend-icon="mdi-account-group"
                        :append-icon="
                            isExpanded ? 'mdi-menu-up' : 'mdi-menu-down'
                        "
                        variant="outlined"
                        @click="expand"
                    >
                        Group members
                    </v-btn>
                    <div ref="expantionPanel" id="expantionPanel">
                        <v-list class="overflow-hidden" lines="one">
                            <router-link
                                v-for="(user, index) in groupMembers"
                                :key="index"
                                :to="{
                                    name: 'chat',
                                    params: { type: 'private', id: user.id },
                                }"
                                class="d-flex mb-2"
                            >
                                <v-list-item
                                    :title="user.name"
                                    :subtitle="user.email"
                                    prepend-avatar="https://cdn-icons-png.flaticon.com/512/147/147144.png"
                                ></v-list-item>
                            </router-link>
                        </v-list>
                    </div>
                </div>

                <!-- leave group -->
                <v-btn
                    prepend-icon="mdi-logout-variant"
                    variant="outlined"
                    class="mb-3"
                    @click="leaveGroup"
                >
                    Leave group
                </v-btn>
            </div>
        </main>

        <!-- display image in full size -->
        <div
            class="image-full-size-container"
            :class="isImageOpened ? 'd-flex' : 'd-none'"
            @click="isImageOpened = false"
        >
            <img src="" ref="fullSizeImage" alt="" srcset="" />
        </div>
    </div>
</template>

<script>
import axiosClient from "../axios";
import store from "../store";
import sound2 from "../../audio/sound2.wav";
import message from "../../audio/message.mp3";
import EmojiPicker from "vue3-emoji-picker";
import { VideoPlayer } from "vue-md-player";
import moment from "moment";

export default {
    components: {
        EmojiPicker,
        VideoPlayer,
    },
    data() {
        return {
            userId: null,
            moment: moment,
            recievedMessage: "",
            myMessage: "",
            messageStatus: null,
            messages: [],
            onlineUsers: [],
            friend: null,
            group: null,
            waveMessage: null,
            sound: sound2,
            messageSound: message,
            isImageOpened: false,
            cursorPosition: 0,
            pagination: 0,
            isLoadingMessages: false,
            isDisplaySeeMoreBtn: true,
            isExpanded: false,
            groupMembers: [],
            dialog: false,
            selectedNewMembers: [],
            suggestedUsers: [],
            memberSearchField: null,
            isLoadingSearch: false,
            isLoadingDialog: false,
        };
    },
    computed: {
        convType() {
            return this.$route.params.type;
        },
        user() {
            return store.state.user.data;
        },
    },

    watch: {
        "$route.params": {
            handler(params) {
                this.messages = [];
                this.pagination = 0;
                this.isDisplaySeeMoreBtn = true;

                if (params.type === "group") {
                    this.friend = null;
                    this.getOnlineUsers();
                    this.getGroup().then(() => this.setPageTitle());
                } else {
                    this.onlineUsers = [];
                    this.getFriend().then(() => this.setPageTitle());
                }

                this.getMessages();
                this.getUserId().then(() => {
                    this.receiveMessage();
                    this.receiveWave();
                });
            },
            immediate: true,
        },

        waveMessage(n, o) {
            setTimeout(function () {
                this.waveMessage = null;
            }, 3000);
        },
    },
    methods: {
        setPageTitle() {
            document.title +=
                this.convType == "private"
                    ? ` - ${this.friend.name}`
                    : ` - ${this.group.name}`;
        },

        async getUserId() {
            try {
                const result = await axiosClient.get("/auth-user");
                this.userId = result.data.id;
            } catch (err) {
                console.log(err);
            }
        },

        async sendMessage(direction) {
            if (!this.myMessage) {
                return;
            }
            if (direction === "out") {
                const messageIndex =
                    this.messages.push({
                        body: this.myMessage,
                        type: "text",
                        author: "me",
                        status: "pending",
                        created_at: this.moment().toISOString(),
                    }) - 1;

                const message = this.myMessage;
                this.myMessage = "";
                this.scrollToBottom();

                try {
                    const result = await axiosClient.post(
                        "/chat/message",
                        {
                            message,
                            type: "text",
                            friend_id:
                                this.$route.params.type == "private"
                                    ? this.$route.params.id
                                    : null,
                            group_id:
                                this.$route.params.type == "group"
                                    ? this.$route.params.id
                                    : null,
                        },
                        {
                            headers: {
                                "X-Socket-Id": Echo.socketId(),
                            },
                        }
                    );
                    this.messages[messageIndex].body = result.data.message; //display message after sanitization
                    this.messages[messageIndex].status = "sent";
                } catch (err) {
                    this.messages[messageIndex].status = "failed";
                }
            } else {
                alert("something went wrong");
            }
        },

        sendMediaMessage({ target }) {
            for (let i = 0; i < target.files.length; i++) {
                const formData = new FormData();

                const file = target.files[i];
                const type = file.type.split("/")[0];
                const friend_id =
                    this.convType == "private" ? this.$route.params.id : "";
                const group_id =
                    this.convType == "group" ? this.$route.params.id : "";
                formData.append("media", file);
                formData.append("type", type);
                formData.append("friend_id", friend_id);
                formData.append("group_id", group_id);

                const url = URL.createObjectURL(file);

                const messageIndex =
                    this.messages.push({
                        body: url,
                        type: type,
                        author: "me",
                        status: "pending",
                        created_at: this.moment().toISOString(),
                    }) - 1;
                this.scrollToBottom();

                axiosClient
                    .post("/chat/message", formData, {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            "X-Socket-Id": Echo.socketId(),
                        },
                    })
                    .then(() => {
                        this.messages[messageIndex].status = "sent";
                    })
                    .catch((err) => {
                        this.messages[messageIndex].status = "failed";
                    });
            }

            target.value = "";
        },

        receiveMessage() {
            const convType = this.$route.params.type;
            const recipientId = this.$route.params.id;
            const userId = this.userId;
            const channelName =
                convType == "private"
                    ? Math.max(userId, recipientId) +
                      "-" +
                      Math.min(userId, recipientId)
                    : recipientId; //get something like 17-36 if conv is private

            Echo.private(`chat.${convType}.${channelName}`).listen(
                "MessageSent",
                (e) => {
                    this.messages.push({
                        body: e.message.content,
                        type: e.message.type,
                        author: e.user.name,
                        created_at: e.message.created_at,
                    });
                    this.scrollToBottom();

                    //play sound
                    var audio = new Audio(this.messageSound); // path to file
                    audio.volume = 0.3;
                    audio.play();
                }
            );
        },

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

        async getFriend() {
            try {
                const result = await axiosClient.get(
                    `/user/${this.$route.params.id}`
                );
                this.friend = result.data;
            } catch (err) {
                console.log(err);
            }
        },

        async getGroup() {
            try {
                const result = await axiosClient.get(
                    `/group/${this.$route.params.id}`
                );
                this.group = result.data;
            } catch (err) {
                console.log(err);
            }
        },

        scrollToBottom(time = 100) {
            setTimeout(() => {
                const element = this.$refs.chatArea;
                element.scrollTop = element.scrollHeight;
            }, time);
        },

        getMessages(scrollToBottom = true) {
            this.pagination++;
            this.isLoadingMessages = true;

            const type = this.$route.params.type;
            const id = this.$route.params.id;
            axiosClient
                .get(`/chat/messages/${type}/${id}?page=${this.pagination}`)
                .then((result) => {
                    if (result.data) {
                        const messages = result.data.data.reverse();
                        const authUserId = store.state.user.data.id;
                        const newMessages = messages.map((data) => ({
                            body: data.message,
                            type: data.type,
                            author:
                                authUserId === data.sender_id
                                    ? "me"
                                    : data.user_name,
                            created_at: data.created_at,
                        }));
                        this.messages.unshift(...newMessages);

                        if (this.messages.length == result.data.total) {
                            this.isDisplaySeeMoreBtn = false;
                        }
                    }
                })
                .catch((err) => {
                    console.log(err);
                })
                .then((result) => {
                    scrollToBottom ? this.scrollToBottom() : null;
                    this.isLoadingMessages = false;
                });
        },

        wave(event, userId) {
            event.preventDefault();
            const element = event.target;
            element.classList.remove("wave"); // reset animation
            void element.offsetWidth; // trigger reflow
            element.classList.add("wave"); // start animation

            axiosClient.post(
                "/chat/wave/" + userId,
                {
                    message:
                        "<span class='font-weight-bold'>" +
                        store.state.user.data.name +
                        "</span> waved at you",
                },
                {
                    headers: {
                        "X-Socket-Id": Echo.socketId(),
                    },
                }
            );
        },

        receiveWave() {
            const userId = this.userId;
            Echo.private(`chat.wave.${userId}`).listen("WaveSent", (e) => {
                this.waveMessage = e.message;
                const element = document.getElementById("wave-message");
                element.classList.remove("show"); // reset animation
                void element.offsetWidth; // trigger reflow
                element.classList.add("show"); // start animation

                //play sound
                var audio = new Audio(this.sound); // path to file
                audio.volume = 0.1;
                audio.play();
            });
        },

        onSelectEmoji(emoji) {
            this.myMessage = [
                this.myMessage.slice(0, this.cursorPosition),
                emoji.i,
                this.myMessage.slice(this.cursorPosition),
            ].join("");
            this.cursorPosition += emoji.i.length; //emojies takes 2 positions each
        },

        openImage(src) {
            this.isImageOpened = true;
            this.$refs.fullSizeImage.src = src;
        },

        setCursorPosition() {
            this.cursorPosition = this.$refs.messageField.selectionStart;
        },

        downloadMedia(url) {
            const link = document.createElement("a");
            const fileName = url.split("/").pop(); //last element
            link.href = url;
            link.setAttribute("download", fileName); //or any other extension
            document.body.appendChild(link);
            link.click();
        },

        expand() {
            this.getGroupMembers().then(() => {
                const el = this.$refs.expantionPanel;
                this.$refs.expantionPanel.classList.toggle("active");
                this.isExpanded = !this.isExpanded;
            });
        },

        async getGroupMembers() {
            try {
                const result = await axiosClient.get(
                    `/group-members/${this.group.id}`
                );
                this.groupMembers = result.data;
            } catch (err) {
                console.log(err);
            }
        },

        async sendInfoMessage(message) {
            try {
                await axiosClient.post(
                    "/chat/message",
                    {
                        message: message.body,
                        type: "info",
                        group_id: this.$route.params.id,
                    },
                    {
                        headers: {
                            "X-Socket-Id": Echo.socketId(),
                        },
                    }
                );

                this.messages.push(message);
                this.scrollToBottom();
            } catch (error) {
                console.log(err);
            }
        },

        async leaveGroup() {
            const message = {
                body: `${this.user.name} left the group`,
                type: "info",
                created_at: this.moment().toISOString(),
            };
            try {
                await axiosClient.delete(`/group-members/${this.userId}`);
                await this.sendInfoMessage(message);

                this.$router.push({ name: "dashboard" });
            } catch (err) {
                console.log(err);
            }
        },

        async searchUser() {
            if(!this.memberSearchField) return;
            this.isLoadingSearch = true;
            this.suggestedUsers = [];
            try {
                const result = await axiosClient.get(
                    `group-members/search/${this.group.id}`,
                    {
                        params: {
                            search: this.memberSearchField,
                        },
                    }
                );
                this.suggestedUsers = result.data;
            } catch (error) {}
            this.isLoadingSearch = false;
        },

        async addMember() {
            this.isLoadingDialog = true;
            const userIds = this.selectedNewMembers.map(
                (memeber) => memeber.id
            );
            await axiosClient.post(`/group-members/${this.group.id}`, {
                users: userIds,
            });
            const userNames = this.selectedNewMembers
                .map((memeber) => memeber.name)
                .toString();
            const message = {
                body: `${this.user.name} added ${userNames} to the group`,
                type: "info",
                created_at: this.moment().toISOString(),
            };
            await this.sendInfoMessage(message);
            this.isLoadingDialog = false;
            this.discardDialog();
        },

        discardDialog() {
            this.dialog = false;
            this.selectedNewMembers = [];
            this.suggestedUsers = [];
            this.memberSearchField = null;
        },

        removeSelectedMember(id) {
            this.selectedNewMembers = this.selectedNewMembers.filter(member => member.id !== id)
        }
    },
};
</script>

<style src="../../../node_modules/vue3-emoji-picker/dist/style.css"></style>
<style src="../../../node_modules/vue-md-player/dist/style.css"></style>
<style scope>
.headline {
    text-align: center;
    font-weight: 100;
    color: white;
}
main#app {
    height: 90vh;
    display: flex;
    column-gap: 15px;
}
.chat-area {
    /*   border: 1px solid #ccc; */
    display: flex;
    flex-direction: column-reverse;
    background: white;
    height: 90vh;
    padding: 1em;
    padding-bottom: 100px;
    overflow: auto;
    width: 50%;
    /* margin: 0 auto 2em auto; */
    box-shadow: 2px 2px 5px 2px rgba(0, 0, 0, 0.3);
}
/* chat area scrollbar */
.chat-area::-webkit-scrollbar-track {
    box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
    background-color: #f5f5f5;
    border-radius: 10px;
}

.chat-area::-webkit-scrollbar {
    width: 10px;
    background-color: #f5f5f5;
}

.chat-area::-webkit-scrollbar-thumb {
    border-radius: 10px;
    background-image: -webkit-gradient(
        linear,
        left bottom,
        left top,
        color-stop(0.44, rgb(56, 119, 249)),
        color-stop(0.5, rgb(28, 108, 207)),
        color-stop(0.74, rgb(28, 58, 148))
    );
}
.message {
    width: 45%;
    border-radius: 10px;
    padding: 0.5em;
    /*   margin-bottom: .5em; */
    font-size: 0.8em;
    position: relative;
    word-break: keep-all;
    word-wrap: break-word;
    display: flex;
    flex-direction: column-reverse;
    gap: 5px;
}
.message-out {
    /*background: #407fff;*/
    background-image: -webkit-gradient(
        linear,
        left top,
        right bottom,
        color-stop(0.44, rgb(28, 58, 148)),
        color-stop(0.72, rgb(28, 108, 207)),
        color-stop(0.86, rgb(56, 119, 249))
    );
    color: white;
    margin-left: auto;
    margin-bottom: 10px;
}
.message-in {
    background: #f1f0f0;
    color: black;
    margin-top: 25px;
}

.message-info {
    font-size: 0.9rem;
    padding: 0.8rem;
    color: #8b6767;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 10px 0;
    font-family: "Circular-Loom";
}

.message-status {
    position: absolute;
    bottom: 5px;
    color: #d19e9e;
    left: -25px;
}
.message-author {
    position: absolute;
    top: -18px;
    left: 5px;
    font-weight: 600;
}
.message .date {
    margin-left: auto;
    font-size: 0.7rem;
    font-weight: 500;
}

.message button.download {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: #d19e9e;
    font-size: 1rem;
}

.message-out button.download {
    left: -30px;
}
.message-in button.download {
    right: -30px;
}

#person1-form {
    position: absolute;
    bottom: 0;
    width: 46%;
}
#person1-input {
    padding: 0.5em;
}
.hand:hover {
    color: rgb(70, 70, 129);
}
.hand.wave {
    animation: wave 0.5s linear 3;
}

#wave-message {
    position: absolute;
    top: -50px;
    left: 50%;
    transform: translateX(-50%);
    width: fit-content;
    padding: 10px;
    background: #490b6e94;
    color: #f3f1f1;
    border-radius: 14px;
}

#wave-message.show {
    animation: show 4s ease-in-out 1;
}

#wave-message.show .wave {
    animation: wave 0.5s linear infinite;
}

.chat-area .options {
    position: absolute;
    right: 32px;
    top: 14px;
    display: flex;
    flex-direction: row-reverse;
    align-items: center;
}

.options label {
    cursor: pointer;
}

.options .emoji {
    width: 0;
    height: 0;
}

.emoji .v3-input-picker-root,
.v3-input-emoji-picker
    .v3-input-picker-root
    .v3-input-picker-wrap
    .v3-input-picker-icon {
    position: unset !important;
}

.emoji .v3-emoji-picker-input {
    padding: 0;
    border: none !important;
    display: none;
}

.media-container .image {
    cursor: pointer;
}

.image-full-size-container {
    position: absolute;
    top: 0;
    left: 0;
    height: 100vh;
    width: 100vw;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #0b0c0cda;
}

.image-full-size-container img {
    max-width: 90%;
    max-height: 90%;
    object-fit: cover;
}

.vuemdplayer {
    min-height: 200px;
}

#expantionPanel {
    max-height: 0px;
    overflow: hidden;
    transition: max-height 0.5s ease-in-out 0s;
}

#expantionPanel.active {
    max-height: 500px;
    overflow-y: auto;
}

.suggested-members label {
    cursor: pointer;
}

.suggested-members .v-input__details {
    display: none;
}

.suggested-members .v-checkbox .v-selection-control {
    height: auto;
}

.suggested-members .info {
    width: fit-content;
    margin: 10px;
    padding: 10px;
    background: #f6f6f6;
    color: #9b9595;
}

@keyframes wave {
    0% {
        rotate: 0deg;
    }
    33% {
        rotate: -20deg;
    }
    66% {
        rotate: 15deg;
    }
    100% {
        rotate: 0deg;
    }
}

@keyframes show {
    0% {
        top: -50px;
    }
    20% {
        top: 20px;
    }
    90% {
        top: 20px;
    }
    100% {
        top: -50px;
    }
}

@media only screen and (max-width: 1200px) {
    .users-list {
        left: -70px;
    }
}
</style>
