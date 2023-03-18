<template>
    <div class="options">
        <!-- add new member -->
        <div class="bg-white mb-3">
            <v-dialog v-model="dialog" persistent>
                <template v-slot:activator="{ props }">
                    <v-btn
                        prepend-icon="mdi-account-plus-outline"
                        variant="outlined"
                        class="w-100"
                        v-bind="props"
                    >
                        Add new member
                    </v-btn>
                </template>
                <v-card>
                    <v-progress-linear
                        v-if="isLoadingDialog"
                        color="primary"
                        indeterminate
                    ></v-progress-linear>
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
                                <v-list class="overflow-hidden d-flex flex-wrap">
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

                            <v-list class="overflow-hidden suggested-members">
                                <p class="text-h6">Suggestions</p>
                                <div v-if="!memberSearchField" class="info">
                                    <span class="text-muted">Start typing to get suggestions</span>
                                </div>
                                <div v-else-if="isLoadingSearch" class="info">
                                    <span class="text-muted">Loading ...</span>
                                </div>
                                <div
                                    v-else-if="
                                        memberSearchField &&
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
                            variant="flat"
                            @click="addMember"
                            :disabled="
                                isLoadingDialog ||
                                selectedNewMembers.length == 0
                            "
                        >
                            Add member
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </div>

        <!-- show group members -->
        <div class="mb-3 bg-white">
            <v-btn
                prepend-icon="mdi-account-group"
                :append-icon="isExpanded ? 'mdi-menu-up' : 'mdi-menu-down'"
                variant="outlined"
                @click="expand"
                class="w-100"
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
        <div class="mb-3 bg-white">
            <v-btn
                prepend-icon="mdi-logout-variant"
                variant="outlined"
                class="w-100"
                @click="leaveGroup"
            >
                Leave group
            </v-btn>
        </div>
    </div>
</template>

<script>
import axiosClient from "../../axios";
import store from "../../store";
import moment from "moment";
export default {
    props: {
        group: Object,
    },

    data() {
        return {
            isExpanded: false,
            groupMembers: [],
            dialog: false,
            selectedNewMembers: [],
            suggestedUsers: [],
            memberSearchField: null,
            isLoadingSearch: false,
            isLoadingDialog: false,
            moment: moment,
        };
    },

    computed: {
        user() {
            return store.state.user.data;
        },
    },

    methods: {
        async leaveGroup() {
            const message = {
                body: `${this.user.name} left the group`,
                type: "info",
                created_at: this.moment().toISOString(),
            };
            try {
                await axiosClient.delete(`/group-members/${this.userId}`);
                this.$emit("sendInfoMessage", message);
                //await this.sendInfoMessage(message);

                this.$router.push({ name: "dashboard" });
            } catch (err) {
                console.log(err);
            }
        },

        async searchUser() {
            if (!this.memberSearchField) return;
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
            this.$emit("sendInfoMessage", message);
            //await this.sendInfoMessage(message);
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
            this.selectedNewMembers = this.selectedNewMembers.filter(
                (member) => member.id !== id
            );
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
    },
};
</script>

<style scoped>
#expantionPanel {
    max-height: 0px;
    overflow: hidden;
    transition: all 0.5s ease-in-out 0s;
    border: none;
}

#expantionPanel.active {
    max-height: 1500px;
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
</style>
