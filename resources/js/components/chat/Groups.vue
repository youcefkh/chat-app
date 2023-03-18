<template>
    <div class="d-flex flex-column h-100">
        <header class="d-flex justify-content-between align-items-center">
            <h4 class="title mb-0">Groups</h4>

            <div>
                <div class="mb-3">
                    <v-dialog v-model="dialog" persistent>
                        <template v-slot:activator="{ props }">
                            <div
                                data-title="Create group"
                                data-title-position="bottom"
                            >
                                <v-btn
                                    icon="mdi-account-multiple-plus-outline"
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
                                <span class="ml-1 text-h5">Add new member</span>
                            </v-card-title>
                            <v-card-text>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12">
                                            <h6>Group picture</h6>
                                            <label for="group-picture" class="group-picture">
                                                <img :src="groupPicture" alt="">
                                            </label>
                                            <input type="file" id="group-picture" ref="groupPictureInput" accept="image/*" hidden @change="setGroupPicture">
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="12">
                                            <h6>Group name</h6>
                                            <v-text-field
                                                label="Name"
                                                required
                                                v-model="groupName"
                                            ></v-text-field>
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col cols="12">
                                            <h6>Group members</h6>
                                            <v-btn @click="isExpand = !isExpand" variant="outlined" color="primary"> Select members </v-btn>
                                        </v-col>
                                    </v-row>

                                    <v-row class="add-members-container" :class="{'active': isExpand}">
                                        <v-col cols="12">
                                            <div class="border p-3 rounded">
                                                <div>
                                                    <v-text-field
                                                        label="Search users"
                                                        required
                                                        @keyup="searchUser"
                                                        v-model="memberSearchField"
                                                    ></v-text-field>
                                                </div>

                                                <v-row>
                                                    <v-col cols="12" class="selected-members">
                                                        <v-list
                                                            class="overflow-hidden d-flex flex-wrap"
                                                        >
                                                            <v-chip
                                                                class="ma-2"
                                                                closable
                                                                v-for="(
                                                                    user, index
                                                                ) in selectedNewMembers"
                                                                :key="index"
                                                                @click="
                                                                    removeSelectedMember(
                                                                        user.id
                                                                    )
                                                                "
                                                            >
                                                                <v-list-item
                                                                    class="p-0"
                                                                    :title="
                                                                        user.name"
                                                                    :prepend-avatar="
                                                                        user.picture"
                                                                ></v-list-item>
                                                            </v-chip>
                                                        </v-list>
                                                    </v-col>
                                                </v-row>

                                                <v-row>
                                                    <v-col cols="12">
                                                        <v-list
                                                            class="overflow-hidden suggested-members"
                                                        >
                                                            <p class="text-h6">
                                                                Suggestions
                                                            </p>
                                                            <div
                                                                v-if="
                                                                    !memberSearchField
                                                                "
                                                                class="info"
                                                            >
                                                                <span class="text-muted"
                                                                    >Start typing to get
                                                                    suggestions</span
                                                                >
                                                            </div>
                                                            <div
                                                                v-else-if="
                                                                    isLoadingSearch
                                                                "
                                                                class="info"
                                                            >
                                                                <span class="text-muted">Loading ...</span>
                                                            </div>
                                                            <div
                                                                v-else-if="
                                                                    memberSearchField &&
                                                                    suggestedUsers.length ==
                                                                        0
                                                                "
                                                                class="info"
                                                            >
                                                                <span class="text-muted"
                                                                    >No results
                                                                    found</span
                                                                >
                                                            </div>
                                                            <v-row
                                                                v-else
                                                                v-for="(
                                                                    user, index
                                                                ) in suggestedUsers"
                                                                :key="index"
                                                                class="m-0 justify-space-between"
                                                            >
                                                                <v-col
                                                                    cols="8"
                                                                    class="p-0"
                                                                >
                                                                    <label
                                                                        :for="user.id"
                                                                        class="w-100"
                                                                    >
                                                                        <v-list-item
                                                                            :title="user.name"
                                                                            :subtitle="user.email"
                                                                            :prepend-avatar="user.picture"
                                                                        ></v-list-item>
                                                                    </label>
                                                                </v-col>
                                                                <v-col
                                                                    cols="4"
                                                                    class="p-0"
                                                                >
                                                                    <v-checkbox
                                                                        v-model="
                                                                            selectedNewMembers"
                                                                        :value="user"
                                                                        :id="
                                                                            user.id + ''"
                                                                    ></v-checkbox>
                                                                </v-col>
                                                            </v-row>
                                                        </v-list>
                                                    </v-col>
                                                </v-row>
                                            </div>
                                        </v-col>
                                    </v-row>
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
                                    @click="createGroup"
                                    :disabled="isLoadingDialog || groupName === ''"
                                >
                                    Create group
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </div>
            </div>
        </header>

        <v-text-field
            class="search m-auto my-8 w-100 flex-grow-0"
            placeholder="Search groups"
            prepend-inner-icon="mdi-magnify"
            v-model="searchValue"
            @keyup="search"
        ></v-text-field>

        <div class="page-content groups-container mt-15 flex-1 overflow-auto">
            <div
                class="group-block d-flex align-items-center gap-2 mb-5 p-2 rounded"
                v-for="group in groups"
                :key="group.id"
                @click="openConversation(group.id)"
            >
                <thumbnail :image="group.picture" :onlineIcon="false" />
                <div class="flex-grow-1">
                    <h5 class="text-truncate font-size-15 mb-0">
                        #{{ group.name }}
                    </h5>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axiosClient from "../../axios";
import Thumbnail from "./Thumbnail.vue";
export default {
    components: { Thumbnail },
    data() {
        return {
            searchValue: null,
            groups: [],
            groupsBis: [],
            groupName: '',
            dialog: false,
            selectedNewMembers: [],
            suggestedUsers: [],
            groupPicture: '/media/default-group-pic.png',
            memberSearchField: null,
            isLoadingSearch: false,
            isLoadingDialog: false,
            isExpand: false
        };
    },

    methods: {
        search() {
            this.groups = this.groupsBis.filter(({ name }) => {
                return (
                    name.toLowerCase().indexOf(this.searchValue.toLowerCase()) >
                    -1
                );
            });
        },

        async getGroups() {
            try {
                const response = await axiosClient.get("/group");
                this.groups = response.data;
                this.groupsBis = this.groups;
            } catch (error) {
                console.log(error);
            }
        },

        async searchUser() {
            if (!this.memberSearchField) return;
            this.isLoadingSearch = true;
            this.suggestedUsers = [];
            try {
                const result = await axiosClient.get(`user/search`, {
                    params: {
                        search: this.memberSearchField,
                    },
                });
                this.suggestedUsers = result.data;
            } catch (error) {}
            this.isLoadingSearch = false;
        },

        async createGroup() {
            this.isLoadingDialog = true;
            const formData = new FormData();
            const image = this.$refs.groupPictureInput.files[0];

            formData.append("picture", image);
            formData.append("name", this.groupName);

            const userIds = this.selectedNewMembers.map(
                (memeber) => memeber.id
            );

            try {
                //create group
                const result = await axiosClient.post("/group", formData, {
                                    headers: {
                                        "Content-Type": "multipart/form-data"
                                    },
                                });

                //add members
                if(userIds.length > 0){
                    await axiosClient.post(`/group-members/${result.data.id}`, {
                        users: userIds,
                    });
                }

                this.getGroups();

            } catch (error) {
                console.log(error);
            }

            this.isLoadingDialog = false;
            this.discardDialog();
        },

        discardDialog() {
            this.dialog = false;
            this.groupPicture = '/media/default-group-pic.png';
            this.groupName = '';
            this.selectedNewMembers = [];
            this.suggestedUsers = [];
            this.memberSearchField = null;
        },

        removeSelectedMember(id) {
            this.selectedNewMembers = this.selectedNewMembers.filter(
                (member) => member.id !== id
            );
        },

        openConversation(id) {
            const query = this.$route.query;
            this.$router.push({
                name: "dashboard",
                params: { type: "group", id },
                query,
            });
        },

        setGroupPicture({target}) {
            const image = target.files[0];
            const url = URL.createObjectURL(image);

            this.groupPicture = url;
        }
    },

    mounted() {
        this.getGroups();
    },
};
</script>

<style scoped>
.add-members-container {
    overflow: hidden;
    max-height: 0px;
    transition: max-height .3s linear;
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

.group-block {
    cursor: pointer;
}

.group-block:hover {
    background-color: #e6ebf5;
}

.group-picture {
    display: block;
    height: 120px;
    width: 120px;
    margin: auto;
    overflow: hidden;
    border: 5px solid #f1f2f3;
    border-radius: 50%;
    cursor: pointer;
}

.group-picture img {
    height: 100%;
    width: 100%;
    object-fit: cover;
}
</style>
