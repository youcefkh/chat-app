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
            <div v-for="n in 10" :key="n" class="user-block">
                <div class="wrapper">
                    <div class="img-container">
                        <img
                            src="https://cdn-icons-png.flaticon.com/512/147/147144.png"
                            alt=""
                        />
                    </div>
                    <div class="online-icon"></div>
                </div>
                <h5 class="font-size-13 text-truncate mt-auto">youcef</h5>
            </div>
        </section>
    </div>
</template>

<script>
import axiosClient from "../../axios";
export default {
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

.img-container {
    height: 40px;
    width: 40px;
    overflow: hidden;
    border-radius: 50%;
}

.img-container img {
    height: 100%;
    width: 100%;
    object-fit: cover;
}

.wrapper {
    position: absolute;
    top: 0;
    transform: translateY(-50%);
}

.online-icon {
    height: 12px;
    width: 12px;
    position: absolute;
    bottom: 0;
    right: 0;
    border: 2px solid #fff;
    border-radius: 50%;
    transform: translateX(20%);
    background-color: #60d960;
}

</style>
