<template>
    <div class="d-flex flex-column h-100">
        <header class="d-flex justify-content-between align-items-center">
            <h4 class="title mb-0">contacts</h4>
        </header>

        <v-text-field
            class="search m-auto my-8 w-100 flex-grow-0"
            placeholder="Search contacts"
            prepend-inner-icon="mdi-magnify"
            v-model="searchValue"
            @keyup="search"
        ></v-text-field>

        <div class="page-content contacts-container mt-15 flex-1 overflow-auto">
            <group-skeleton v-if="isLoadingContacts" />
            <div v-else>
                <p v-if="Object.keys(contacts).length == 0" class="text-muted text-center">You don't have any contacts</p>
                <div v-for="(group, key) in contacts" :key="key">
                    <h5 class="p-2 text-purple-lighten-2 font-size-16">{{key}}</h5>
                    <div
                        class="contact-block d-flex align-items-center gap-2 mb-5 p-2 rounded"
                        v-for="contact in group"
                        :key="contact.id"
                        @click="openConversation(contact.id)"
                    >
                        <thumbnail
                            :image="contact.picture"
                            :onlineIcon="false"
                        />
                        <div class="flex-grow-1">
                            <h5 class="text-truncate font-size-15 mb-0">
                                {{ contact.name }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { isProxy, toRaw } from 'vue';
import axiosClient from "../../axios";
import GroupSkeleton from "../skeletons/GroupSkeleton.vue";
import Thumbnail from "./Thumbnail.vue";
export default {
    components: { Thumbnail, GroupSkeleton },
    data() {
        return {
            searchValue: null,
            contacts: {},
            contactsBis: {},
            isLoadingContacts: false,
        };
    },

    methods: {
        search() {
            if(!this.searchValue){
                this.contacts = this.contactsBis;
                return;
            }
            let arr = [];
            Object.values(this.contactsBis).forEach(element => {
                let result = element.filter(({ name }) => {
                    return (
                        name.toLowerCase().indexOf(this.searchValue.toLowerCase()) >
                        -1
                    );
                });
                arr.push(...result)
            });
            this.contacts = this.groupByLetter(arr);
        },

        async getContacts() {
            this.isLoadingContacts = true;
            try {
                const response = await axiosClient.get("/contact");
                this.contacts = this.groupByLetter(response.data);
                this.contactsBis = this.contacts
                this.isLoadingContacts = false;
            } catch (error) {
                console.log(error);
            }
        },

        groupByLetter(array) {
            return array.reduce((store, contact) => {
                const letter = contact.name.charAt(0).toUpperCase();
                const keyStore =
                    store[letter] || // Does it exist in the object?
                    (store[letter] = []); // If not, create it as an empty array
                keyStore.push(contact);

                return store;
            }, {});
        },

        openConversation(id) {
            const query = this.$route.query;
            this.$router.push({
                name: "dashboard",
                params: { type: "private", id },
                query,
            });
        },
    },

    mounted() {
        this.getContacts();
    },
};
</script>

<style scoped>
.add-members-container {
    overflow: hidden;
    max-height: 0px;
    transition: max-height 0.3s linear;
}
.add-members-container.active {
    max-height: 100vh;
}
.suggested-members .info {
    width: fit-content;
    margin: 10px;
    padding: 10px;
    background: #f6f6f6;
}
label {
    cursor: pointer;
}

.contact-block {
    cursor: pointer;
}

.contact-block:hover {
    background-color: #e6ebf5;
}
</style>
