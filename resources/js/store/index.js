import { createStore } from "vuex";
import axiosClient from "../axios";
import Echo from "laravel-echo";

import Pusher from "pusher-js";

const store = createStore({
    state: {
        user: {
            data: {},
            token: null,
        },
        notifications: {}
    },
    mutations: {
        setUserData(state, payload) {
            state.user.data = payload;
        },
        setToken(state, payload) {
            state.user.token = payload;
        },
    },
    actions: {
        setStoredState({ commit, dispatch }) {
            const token = localStorage.getItem("token");
            if (token) {
                commit("setToken", token);
                dispatch("setUserData");
            }
        },
        setUserData({ commit }) {
            //fetch user data
            axiosClient
                .get("/auth-user")
                .then((res) => {
                    commit("setUserData", res.data);
                })
                .catch((err) => console.log(err));
        },
        setToken({ commit }, payload) {
            if(payload){
                commit("setToken", payload);
                localStorage.setItem("token", payload);
            }
        },
        resetUser({ state }) {
            //for logout
            localStorage.removeItem("token");
            state.user = {
                data: {},
                token: null,
            };
        },
        initializeEcho({ state, dispatch }) {
            window.Pusher = Pusher;

            window.Echo = new Echo({
                broadcaster: "pusher",
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                wsHost:
                    import.meta.env.VITE_PUSHER_HOST ??
                    `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
                wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
                wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
                forceTLS: false,
                encrypted: true,
                enabledTransports: ["ws", "wss"],
                disableStats: false, //false: when using laravel websockets
                /* auth: {
                    headers: {
                        Authorization: `Bearer ${state.user.token}`,
                        Accept: "application/json",
                    },
                }, */
                authorizer: (channel, options) => {
                    return {
                        authorize: (socketId, callback) => {
                            axios.post('/api/broadcasting/auth',
                            {
                                socket_id: socketId,
                                channel_name: channel.name
                            },
                            {headers: {
                                'Authorization': `Bearer ${state.user.token}`,
                                'Accept'       : 'application/json'
                            }
                        })
                            .then(response => {
                                callback(null, response.data);
                            })
                            .catch(error => {
                                callback(error);
                            });
                        }
                    };
                },
            });
        },
    },
});

export default store;
