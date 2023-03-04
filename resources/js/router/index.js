import { createRouter, createWebHistory } from "vue-router";
import Login from "../components/Login.vue";
import Register from "../components/Register.vue";
import Dashboard from "../components/Dashboard.vue";
import Game from "../components/Game.vue";
import Chat from "../components/Chat.vue";
import UsersList from "../components/UsersList.vue";

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
        path: "/dashbord/chat/:type/:id",
        name: "dashboard",
        component: Dashboard,
        meta: { requiresAuth: true },
    },
    {
        path: "/game",
        name: "game",
        component: Game,
        meta: { requiresAuth: true },
    },
    {
        path: "/test",
        name: "test",
        component: UsersList,
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

router.beforeEach(async (to, from, next) => {
    let userId = null;
    if(store.state.user.data.id) {
        userId = store.state.user.data.id
    }else {
        try {
            const result = await axiosClient.get('/auth-user');
            userId = result.data ? result.data.id : null;
        } catch (error) {
            console.log("you are logged out");
        }
    }

    //set page title
    document.title = to.name.charAt(0).toUpperCase() + to.name.slice(1); //capitalize first letter

    if (to.meta.requiresAuth && !store.state.user.token) {
        next({ name: "login" });

    } else if (!to.meta.requiresAuth && store.state.user.token) {
        //for login and register
        next({ name: "dashboard" });

    }else if(to.name == 'chat' && to.params.type == 'group') {
        //protect chat groups
        const result = await axiosClient.get(`/group-members/${to.params.id}/${userId}`)
        !result.data.isMember ? next({ name: "dashboard" }) : next();

    }else {
        next();
    }


    //random request to the server to keep on track the user's last activity whenever he opens a new page
    axiosClient.get("/track-activity")
});

export default router;
