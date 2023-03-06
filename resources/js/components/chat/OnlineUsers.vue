<template>
    <div>
        <section
            id="users-wrapper"
            ref="el"
            :style="{
                cursor: isDragging ? 'grabbing' : 'grab',
                scrollSnapType: isDragging ? '' : '',
            }"
            @mousedown="onMouseDown"
            @mouseup="onMouseUp"
        >
            <div v-for="user in onlineUsers" :key="user.id" class="user-block">
                <thumbnail class="thumbnail" :onlineIcon="true" :image="user.picture"/>
                <h5 class="font-size-13 text-truncate mt-auto">{{user.name}}</h5>
            </div>
        </section>
    </div>
</template>

<script>
import axiosClient from "../../axios";
import Thumbnail from './Thumbnail.vue';
export default {
  components: { Thumbnail },
    props: {
        onlineUsers: Array,
    },

    data() {
        return {
            isDragging: false,
            cursorPos: [0, 0],
            el: null,
        };
    },

    mounted() {
        window.addEventListener("mouseup", this.onMouseUp);
    },

    destroyed() {
        window.removeEventListener("mouseup", this.onMouseUp);
    },

    methods: {
        /** @param {MouseEvent} ev */
        onMouseDown(ev) {
            this.cursorPos = [ev.pageX, ev.pageY];
            this.isDragging = true;

            window.addEventListener("mousemove", this.onMouseHold);
        },

        /** @param {MouseEvent} ev */
        onMouseUp(ev) {
            /* console.log({ x: ev.clientX, y: ev.clientY });
            console.log(this.$refs.el.scrollLeft);
            console.log(this.$refs.el.scrollWidth);
            console.log(this.$refs.el.scrollWidth - this.$refs.el.clientWidth); */
            window.removeEventListener("mousemove", this.onMouseHold);
            this.isDragging = false;
        },

        /** @param {MouseEvent} ev */
        onMouseHold(ev) {
            ev.preventDefault();

            requestAnimationFrame(() => {
                const delta = [
                    ev.pageX - this.cursorPos[0],
                    ev.pageY - this.cursorPos[1],
                ];

                this.cursorPos = [ev.pageX, ev.pageY];

                if (!this.$refs.el) return;
                this.$refs.el.scrollBy({
                    left: -delta[0],
                    top: -delta[1],
                });
            });
        },
    },
};
</script>

<style scoped>
#users-wrapper {
    width: 100%;
    height: 72px;
    overflow-x: auto;
    display: flex;
    align-items: flex-end;
}

#users-wrapper::-webkit-scrollbar {
    display: none;
}

.user-block {
    min-width: 80px;
    height: 50px;
    text-align: center;
    scroll-snap-align: start;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #e6ebf5;
    margin-right: 1rem;
    border-radius: 10%;
    position: relative;
}

.thumbnail {
    position: absolute !important;
    top: 0;
    transform: translateY(-50%);
}
</style>
