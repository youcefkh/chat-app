import { createRouter, createWebHistory } from "vue-router";
import Login from "../components/Login.vue";
import Register from "../components/Register.vue";
import Dashboard from "../components/Dashboard.vue";
import MyAccount from "../components/MyAccount.vue";
import UsersList from "../components/UsersList.vue";
import Game from "../components/Game.vue";
import Chat from "../components/Chat.vue";
import store from "../store";
import axios from "axios";
import axiosClient from "../axios";

const routes = [
    {
        path: "/login",
        name: "login",
        component: Login,
        meta: { requiresAuth: false },
    },
    {
        path: "/register",
        name: "register",
        component: Register,
        meta: { requiresAuth: false },
    },
    {
        path: "/",
        name: "dashboard",
        component: Dashboard,
        meta: { requiresAuth: true },
    },
    {
        path: "/my-account",
        name: "account",
        component: MyAccount,
        meta: { requiresAuth: true },
    },
    {
        path: "/users",
        name: "users",
        component: UsersList,
        meta: { requiresAuth: true },
    },
    {
        path: "/game",
        name: "game",
        component: Game,
        meta: { requiresAuth: true },
    },
    {
        path: "/chat/:type/:id",
        name: "chat",
        component: Chat,
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !store.state.user.token) {
        next({ name: "login" });
    } else if (!to.meta.requiresAuth && store.state.user.token) {
        //for login and register
        next({ name: "dashboard" });
    } else {
        next();
    }

    //random request to the server to keep on track the user's last activity whenever he opens a new page
    axiosClient.get("/track-activity")
});

export default router;
