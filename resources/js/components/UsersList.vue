<template>
    <div>
        <section
            ref="el"
            style="width: 50%; height: 400px; overflow-x: auto; display: flex"
            :style="{
                cursor: isDragging ? 'grabbing' : 'grab',
                scrollSnapType: isDragging ? '' : '',
            }"
            @mousedown="onMouseDown"
            @mouseup="onMouseUp"
        >
            <div
                v-for="nr in 20"
                :key="nr"
                style="
                    min-width: 200px;
                    min-height: 500px;
                    text-align: center;
                    scroll-snap-align: start;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background-color: #eee;
                    margin-right: 1rem;
                "
            >
                <span>
                    {{ nr }}
                </span>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    name: "App",
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
            console.log({ x: ev.clientX, y: ev.clientY });
            console.log(this.$refs.el.scrollLeft);
            console.log(this.$refs.el.scrollWidth);
            console.log(this.$refs.el.scrollWidth - this.$refs.el.clientWidth);
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

<style>
</style>
