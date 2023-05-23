<template>
    <div class="d-flex">
        <!-- waving notification message -->
        <div id="wave-message">
            <span v-html="waveMessage"></span>
            <v-icon class="wave ml-2" icon="mdi-hand-wave" />
        </div>

        <!-- wave
                        <v-icon
                            class="hand"
                            icon="mdi-hand-wave"
                            :title="'wave at ' + friend.name"
                            @click="wave($event, friend.id)"
                        />
            -->
        <main id="app" class="position-relative">
            <header class="border-bottom p-4 d-flex align-items-center justify-content-between">
                <div
                    v-if="conversation"
                    class="d-flex align-items-center gap-3"
                >
                    <thumbnail
                        :image="conversation.picture"
                        :onlineUsers="convType == 'private' ? onlineUsers : null"
                        :user_id="convType == 'private' && friend ? friend.id : null"
                        :onlineIcon="convType == 'private' ? true : false"
                        style="scale: 1.2"
                    />
                    <h5 class="mb-0">
                        {{ conversation ? conversation.name : null }}
                    </h5>
                </div>

                <div v-if="!isForbidden">
                    <v-icon class="text-muted chat-options" icon="mdi-dots-horizontal" @click="isDisplayDetails=true"/>
                </div>
            </header>
            <!-- error message -->
            <div v-if="isForbidden" class="error-layer">
                <v-icon icon="mdi-lock" class="icon"/>
                <p>You can't access this conversation</p>
            </div>

            <!-- chat box -->
            <div v-else ref="chatArea" class="chat-area rounded">
                <chat-skeleton v-if="isdisplaySkeleton" />
                <!-- messages -->
                <div v-else>
                    <div
                        v-for="(message, index) in messages"
                        :key="index"
                        class="d-flex align-items-end gap-2"
                        :class="{ 'flex-row-reverse': message.author == 'me' }"
                    >
                        <!-- author thumnail -->
                        <div v-if="message.type !== 'info'">
                            <thumbnail
                                :image="message.thumbnail"
                                :onlineIcon="false"
                                :data-title="message.author"
                                data-title-position="bottom"
                            />
                        </div>

                        <!-- if message is not of type info -->
                        <div
                            v-if="message.type !== 'info'"
                            class="message"
                            :class="{
                                'message-out': message.author === 'me',
                                'message-in': message.author !== 'me',
                            }"
                        >
                            <p class="date mb-0">
                                <v-icon icon="mdi-clock-outline" class="mr-1" />
                                <span
                                    :title="
                                        moment(message.created_at).format('LLL')
                                    "
                                    >{{ formatDate(message.created_at) }}</span
                                >
                            </p>

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
                            <div>
                                <v-icon icon="mdi-clock-outline" class="mr-1" />
                                <span>
                                    {{ formatDate(message.created_at) }}
                                </span>
                            </div>
                            <span v-html="message.body"></span>
                        </div>
                    </div>
                </div>

                <!-- see more button -->
                <div
                    ref="seeMoreBtn"
                    class="see-more my-auto"
                    v-if="isDisplaySeeMoreBtn"
                >
                    <div>
                        <v-btn
                            class="mb-0"
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
                </div>

                <!-- Message input field -->
                <v-form id="person1-form">
                    <v-text-field
                        v-model="myMessage"
                        id="person1-input"
                        :counter="500"
                        placeholder="Type your message"
                        maxlength="500"
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
            <!-- chat details -->
                <chat-details :class="{'active': isDisplayDetails}" ref="chatDetails" :conversation="conversation" :isOnline="isOnline" :convType="convType" @hideDetails="isDisplayDetails=false"/>
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
import GroupOptions from "./chat/GroupOptions.vue";
import dateFormatter from "../mixins/dateFormatter";
import Thumbnail from "./chat/Thumbnail.vue";
import ChatSkeleton from "./skeletons/ChatSkeleton.vue";
import ChatDetails from "./chat/ChatDetails.vue";

export default {
    mixins: [dateFormatter],

    props: {
        chat: Object,
        onlineUsers: Array,
    },

    components: {
        EmojiPicker,
        VideoPlayer,
        GroupOptions,
        Thumbnail,
        ChatSkeleton,
        ChatDetails,
    },

    data() {
        return {
            isForbidden: false,
            convType: null,
            convId: null,
            userId: null,
            moment: moment,
            recievedMessage: "",
            myMessage: "",
            messageStatus: null,
            messages: [],
            friend: null,
            group: null,
            waveMessage: null,
            sound: sound2,
            messageSound: message,
            isImageOpened: false,
            cursorPosition: 0,
            pagination: 0,
            isLoadingMessages: false,
            isDisplaySeeMoreBtn: false,
            isdisplaySkeleton: false,
            isDisplayDetails: false,
        };
    },

    computed: {
        user() {
            return store.state.user.data;
        },

        conversation() {
            if(this.convType == 'private' && this.friend) {
                return this.friend;
            } else if(this.convType == 'group' && this.group) {
                return this.group;
            }else {
                return [];
            }
        },

        isOnline() {
            if(this.convType == 'group') return true;

            return this.onlineUsers.some((user) => {
                return user.id === this.friend.id;
            });
        },
    },

    watch: {
        chat: {
            handler(params) {
                if (this.convType == params.type && this.convId == params.id)
                    return; //when query params change no need to modify chat content

                this.convType = params.type;
                this.convId = params.id;
                this.messages = [];
                this.pagination = 0;
                this.isDisplaySeeMoreBtn = true;
                this.isForbidden = false;

                if (params.type === "group") {
                    this.friend = null;
                    this.getGroup().then(() => this.setPageTitle());
                } else {
                    this.getFriend().then(() => this.setPageTitle());
                }

                this.getMessages();
                this.getUserId().then(() => {
                    this.receiveMessage();
                    this.receiveWave();
                });
                this.seeMessages();
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
                        thumbnail: this.user.picture,
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
                                this.convType == "private" ? this.convId : null,
                            group_id:
                                this.convType == "group" ? this.convId : null,
                        },
                        {
                            headers: {
                                "X-Socket-Id": Echo.socketId(),
                            },
                        }
                    );
                    this.messages[messageIndex].body = result.data.message; //display message after sanitization
                    this.messages[messageIndex].status = "sent";
                    this.triggerMessageEvent();
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
                const friend_id = this.convType == "private" ? this.convId : "";
                const group_id = this.convType == "group" ? this.convId : "";
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
                        thumbnail: this.user.picture,
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
                        this.triggerMessageEvent();
                    })
                    .catch((err) => {
                        this.messages[messageIndex].status = "failed";
                    });
            }

            target.value = "";
        },

        receiveMessage() {
            const convType = this.convType;
            const recipientId = this.convId;
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
                        thumbnail: e.user.picture,
                    });
                    this.scrollToBottom();

                    //play sound
                    var audio = new Audio(this.messageSound); // path to file
                    audio.volume = 0.3;
                    audio.play();

                    //reset notifications counter
                    this.seeMessages();
                }
            );
        },

        async getFriend() {
            try {
                const result = await axiosClient.get(`/user/${this.convId}`);
                this.friend = result.data;
            } catch (err) {
                console.log(err);
            }
        },

        async getGroup() {
            try {
                const result = await axiosClient.get(`/group/${this.convId}`);
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
            this.isdisplaySkeleton = true;
            this.pagination++;
            this.isdisplaySkeleton = this.pagination == 1 ? true : false;
            this.isDisplaySeeMoreBtn = this.pagination == 1 ? false : true;
            this.isLoadingMessages = true;

            const type = this.convType;
            const id = this.convId;
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
                            thumbnail: data.user_pic,
                        }));
                        this.messages.unshift(...newMessages);

                        if (this.messages.length == result.data.total) {
                            this.isDisplaySeeMoreBtn = false;
                        } else {
                            this.isDisplaySeeMoreBtn = true;
                        }
                    }
                })
                .catch((err) => {
                    if(err.request.status == 403) {
                        this.isForbidden = true;
                        return;
                    }
                    console.log(err);
                })
                .then((result) => {
                    scrollToBottom ? this.scrollToBottom() : null;
                    this.isLoadingMessages = false;
                    this.isdisplaySkeleton = false;
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

        async sendInfoMessage(message) {
            try {
                await axiosClient.post(
                    "/chat/message",
                    {
                        message: message.body,
                        type: "info",
                        group_id: this.convId,
                    },
                    {
                        headers: {
                            "X-Socket-Id": Echo.socketId(),
                        },
                    }
                );

                this.messages.push(message);
                this.scrollToBottom();
                this.triggerMessageEvent();
            } catch (error) {
                console.log(err);
            }
        },

        triggerMessageEvent() {
            this.$emit("messageSent");
        },

        async seeMessages() {
            const conversation = {
                type: this.convType,
                id: this.convId,
            };
            try {
                await axiosClient.put("/chat/see-messages", conversation);
                this.$emit("seeMessages", conversation);
            } catch (error) {
                console.log(error);
            }
        },
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
    height: 100vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 4px rgb(15 34 58 / 12%);
    flex-grow: 1;
}

main > header {
    min-height: 89px;
}

.chat-area {
    /*   border: 1px solid #ccc; */
    display: flex;
    flex-direction: column-reverse;
    background: white;
    height: 100vh;
    padding: 1em;
    padding-bottom: 100px;
    overflow-x: hidden;
}
/* chat area scrollbar */
.chat-area::-webkit-scrollbar-track {
    background-color: #f5f5f5;
}

.chat-area::-webkit-scrollbar {
    width: 10px;
    background-color: #f5f5f5;
}

.chat-area::-webkit-scrollbar-thumb {
    border-radius: 10px;
    background: #7269ef;
    /* background-image: -webkit-gradient(
        linear,
        left bottom,
        left top,
        color-stop(0.44, rgb(56, 119, 249)),
        color-stop(0.5, rgb(28, 108, 207)),
        color-stop(0.74, rgb(28, 58, 148))
    ); */
}
.message {
    width: 25%;
    max-width: 45%;
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

.message:has(.media-container) {
    width: 30%;
}

.message-out {
    background: #7269ef;
    /* background-image: -webkit-gradient(
        linear,
        left top,
        right bottom,
        color-stop(0.44, rgb(28, 58, 148)),
        color-stop(0.72, rgb(28, 108, 207)),
        color-stop(0.86, rgb(56, 119, 249))
    ); */
    color: white;
    margin-left: auto;
    margin-top: 15px;
}
.message-in {
    background: #f5f7fb;
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
    text-align: right;
    font-size: 10px;
    font-weight: 500;
}

.message-out .date {
    color: #f5f7fb;
}

.message-in .date {
    color: #7a7f9a;
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
    width: calc(100% - 45px);
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

.see-more {
    width: 100%;
    min-height: 1px;
    background: #ffeaea;
    position: relative;
}

.see-more > div {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 10px;
    background: #fff;
}

.v-input__control {
    background: #f5f7fb6e !important;
}

.chat-options {
    cursor: pointer;
}

.error-layer {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: #000000b5;
    color: #fff;
}

.error-layer .icon {
    font-size: 10rem;
}

.error-layer p {
    font-size: 2rem;
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
    main#app {
        height: 100%;
        width: 100%;
    }
}
</style>
