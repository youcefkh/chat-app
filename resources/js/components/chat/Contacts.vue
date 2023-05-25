<template>
    <div class="d-flex flex-column h-100">
        <header class="d-flex justify-content-between align-items-center">
            <h4 class="title mb-0">contacts</h4>

            <div>
                <div class="mb-3">
                    <v-dialog v-model="dialog" persistent>
                <template v-slot:activator="{ props }">
                    <div
                                data-title="Add Contact"
                                data-title-position="bottom"
                            >
                                <v-btn
                                    icon="mdi-account-plus-outline"
                                    variant="text"
                                    v-bind="props"
                                ></v-btn>
                            </div>
                </template>
                <v-card>
                    <v-progress-linear
                        v-if="isLoadingDialog"
                        color="primary"
                        indeterminate
                    ></v-progress-linear>
                    <v-card-title class="d-flex">
                        <v-icon icon="mdi-account-plus-outline" />
                        <span class="ml-1 text-h5">Add new contact</span>
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field
                                        label="Search"
                                        required
                                        @keyup="searchUser"
                                        v-model="contactSearchField"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <div class="selected-members">
                                <v-chip-group class="overflow-hidden d-flex flex-wrap">
                                    <v-chip
                                        class="ma-2"
                                        append-icon="mdi-close-circle"
                                        v-for="(
                                            user, index
                                        ) in selectedNewContacts"
                                        :key="index"
                                        @click="removeSelectedContact(user.id)"
                                    >
                                        <v-list-item
                                            class="p-0"
                                            :title="user.name"
                                            :prepend-avatar="user.picture"
                                        ></v-list-item>
                                    </v-chip>
                                </v-chip-group>
                            </div>

                            <v-list class="overflow-hidden suggested-members">
                                <p class="text-h6">Suggestions</p>
                                <div v-if="!contactSearchField" class="info">
                                    <span class="text-muted">Start typing to get suggestions</span>
                                </div>
                                <div v-else-if="isLoadingSearch" class="info">
                                    <span class="text-muted">Loading ...</span>
                                </div>
                                <div
                                    v-else-if="
                                        contactSearchField &&
                                        suggestedUsers.length == 0
                                    "
                                    class="info"
                                >
                                    <span class="text-muted">No results found</span>
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
                                                :prepend-avatar="user.picture"
                                            ></v-list-item>
                                        </label>
                                    </v-col>
                                    <v-col cols="4" class="p-0">
                                        <v-checkbox
                                            v-model="selectedNewContacts"
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
                            variant="flat"
                            @click="addContact"
                            :disabled="
                                isLoadingDialog ||
                                selectedNewContacts.length == 0"
                        >
                            Add Contact
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
                </div>
            </div>
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
                <p
                    v-if="Object.keys(contacts).length == 0"
                    class="text-muted text-center"
                >
                    You don't have any contacts
                </p>
                <div v-for="(group, key) in contacts" :key="key">
                    <h5 class="p-2 text-purple-lighten-2 font-size-16">
                        {{ key }}
                    </h5>
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

                        <v-menu>
                            <template v-slot:activator="{ props }">
                                <v-btn
                                    class="text-muted"
                                    variant="text"
                                    icon="mdi-dots-vertical"
                                    size="small"
                                    v-bind="props"
                                ></v-btn>
                            </template>

                            <v-list class="contact-actions">
                                <v-list-item class="p-0">
                                    <v-list-item-title>
                                        <button
                                            class="p-2"
                                            @click="
                                                removeContact(contact.pivot.id)
                                            "
                                        >
                                            <p
                                                class="mr-10 mb-0 d-inline-block"
                                            >
                                                Remove
                                            </p>
                                            <v-icon
                                                class="text-muted"
                                                size="small"
                                                icon="mdi-trash-can-outline"
                                            />
                                        </button>
                                    </v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { isProxy, toRaw } from "vue";
import axiosClient from "../../axios";
import GroupSkeleton from "../skeletons/GroupSkeleton.vue";
import Thumbnail from "./Thumbnail.vue";
import store from "../../store"
export default {
    components: { Thumbnail, GroupSkeleton },
    data() {
        return {
            searchValue: null,
            contacts: {},
            contactsBis: {},
            isLoadingContacts: false,
            dialog: false,
            selectedNewContacts: [],
            suggestedUsers: [],
            contactSearchField: null,
            isLoadingSearch: false,
            isLoadingDialog: false,
        };
    },

    methods: {
        search() {
            if (!this.searchValue) {
                this.contacts = this.contactsBis;
                return;
            }
            let arr = [];
            Object.values(this.contactsBis).forEach((element) => {
                let result = element.filter(({ name }) => {
                    return (
                        name
                            .toLowerCase()
                            .indexOf(this.searchValue.toLowerCase()) > -1
                    );
                });
                arr.push(...result);
            });
            this.contacts = this.groupByLetter(arr);
        },

        async getContacts() {
            this.isLoadingContacts = true;
            try {
                const response = await axiosClient.get("/contact");
                this.contacts = this.groupByLetter(response.data);
                this.contactsBis = this.contacts;
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
            store.commit('setIsShowChat', true)
        },

        async removeContact(id) {
            try {
                await axiosClient.delete(`/contact/${id}`);
                let arr = [];
                Object.values(this.contactsBis).forEach((element) => {
                    let result = element.filter(({ pivot }) => {
                        return pivot.id !== id;
                    });
                    arr.push(...result);
                });
                this.contacts = this.groupByLetter(arr);
                this.contactsBis = this.contacts;

            } catch (error) {
                console.log(error);
            }
        },

        async searchUser() {
            if (!this.contactSearchField) return;
            this.isLoadingSearch = true;
            this.suggestedUsers = [];
            try {
                const result = await axiosClient.get(
                    `contact-suggestions/search`,
                    {
                        params: {
                            search: this.contactSearchField,
                        },
                    }
                );
                this.suggestedUsers = result.data;
            } catch (error) {}
            this.isLoadingSearch = false;
        },

        async addContact() {
            this.isLoadingDialog = true;
            const userIds = this.selectedNewContacts.map(
                (memeber) => memeber.id
            );

            try {
                userIds.forEach(async (id) => {
                    await axiosClient.post(`/contact`, {
                        id: id,
                    });
                });

                /* push new contacts to array */
                let arr = [];
                Object.values(this.contactsBis).forEach((element) => {
                    let result = element;
                    arr.push(...result);
                });

                arr.push(...this.selectedNewContacts);
                
                this.contacts = this.groupByLetter(arr);
                this.contactsBis = this.contacts;
                
                this.isLoadingDialog = false;
                
            } catch (error) {
                console.log(error);
            }

            this.discardDialog();
        },

        discardDialog() {
            this.dialog = false;
            this.selectedNewContacts = [];
            this.suggestedUsers = [];
            this.contactSearchField = null;
        },

        removeSelectedContact(id) {
            this.selectedNewContacts = this.selectedNewContacts.filter(
                (contact) => (contact.id !== id)
            );
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

.contact-actions .v-list-item-title:hover {
    background: #ebedf1;
    cursor: pointer;
}
</style>
