<template>
    <div>
        <h1 class="headline">Chat Box</h1>

        <!-- waving notification message -->
        <div id="wave-message">
            <span v-html="waveMessage"></span>
            <v-icon class="wave ml-2" icon="mdi-hand-wave" />
        </div>

        <main id="app" class="position-relative">
            <section ref="chatArea" class="chat-area rounded">
                <!-- messages -->
                <div>
                    <div
                        v-for="(message, index) in messages"
                        :key="index"
                        class="message"
                        :class="{
                            'message-out': message.author === 'me',
                            'message-in': message.author !== 'me',
                        }"
                    >
                        <span
                            :title="moment(message.created_at).format('LLL')"
                            class="date"
                            >{{ moment(message.created_at).fromNow() }}</span
                        >

                        <!-- display the message -->
                        <div
                            class="media-container"
                            v-if="message.type !== 'text'"
                        >
                            <!-- message as an image -->
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
                </div>

                <!-- see more button -->
                <div ref="seeMoreBtn" class="m-auto">
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
            </section>

            <!-- online users -->
            <aside class="users-list position-absolute top-0">
                <div v-if="!friend">
                    <p class="h4">Online Users</p>
                    <v-list class="overflow-hidden" lines="one">
                        <router-link
                            v-for="(user, index) in users"
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
                    <ul></ul>
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
            </aside>
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
            moment: moment,
            recievedMessage: "",
            myMessage: "",
            messageStatus: null,
            messages: [],
            users: [],
            friend: null,
            waveMessage: null,
            sound: sound2,
            messageSound: message,
            isImageOpened: false,
            cursorPosition: 0,
            pagination: 0,
            isLoadingMessages: false,
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
                if (params.type === "group") {
                    this.friend = null;
                    this.getOnlineUsers();
                } else {
                    this.users = [];
                    this.getFriend();
                }
                this.getMessages();
                this.receiveMessage();
                this.receiveWave();
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
        sendMessage(direction) {
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

                axiosClient
                    .post(
                        "/chat/message",
                        {
                            message,
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
                    )
                    .then((result) => {
                        this.messages[messageIndex].body = result.data.message; //display message after sanitization
                        this.messages[messageIndex].status = "sent";
                    })
                    .catch((err) => {
                        this.messages[messageIndex].status = "failed";
                    });
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
            axiosClient
                .get(`chat/${this.$route.params.type}/${this.$route.params.id}`)
                .then((result) => {
                    Echo.private(
                        `chat.${this.$route.params.type}.${result.data.id}`
                    ).listen("MessageSent", (e) => {
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
                    });
                })
                .catch((err) => {
                    console.log(err);
                });
        },

        getOnlineUsers() {
            axiosClient
                .get("/logged-in-users")
                .then((result) => {
                    this.users = result.data;
                })
                .catch((err) => {
                    console.log(err);
                });

            Echo.private("notifications").listen("UserSessionChange", (e) => {
                if (e.type === "connected") {
                    this.users.push(e.user);
                } else {
                    this.users = this.users.filter(
                        (user) => user.id !== e.user.id
                    );
                }
            });
        },

        getFriend() {
            axiosClient
                .get(`/user/${this.$route.params.id}`)
                .then((result) => {
                    this.friend = result.data;
                })
                .catch((err) => {
                    console.log(err);
                });
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
                                authUserId === data.user_id
                                    ? "me"
                                    : data.user.name,
                            created_at: data.created_at,
                        }));
                        this.messages.unshift(...newMessages);
                        if (this.messages.length == result.data.total) {
                            this.$refs.seeMoreBtn.style.display = "none";
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
            axiosClient
                .get("/auth-user")
                .then((result) => {
                    const userId = result.data.id;
                    Echo.private(`chat.wave.${userId}`).listen(
                        "WaveSent",
                        (e) => {
                            this.waveMessage = e.message;
                            const element =
                                document.getElementById("wave-message");
                            element.classList.remove("show"); // reset animation
                            void element.offsetWidth; // trigger reflow
                            element.classList.add("show"); // start animation

                            //play sound
                            var audio = new Audio(this.sound); // path to file
                            audio.volume = 0.1;
                            audio.play();
                        }
                    );
                })
                .catch((err) => {
                    console.log(err);
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
    },

    mounted() {
        /* if (this.$route.params.type === "group") {
            this.getOnlineUsers();
        } else {
            this.getFriend();
        }
        this.getMessages();
        this.receiveMessage(); */
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
    max-width: 50%;
    margin: 0 auto 2em auto;
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
    margin-left: 50%;
    margin-bottom: 10px;
}
.message-in {
    background: #f1f0f0;
    color: black;
    margin-top: 25px;
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

.options {
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
