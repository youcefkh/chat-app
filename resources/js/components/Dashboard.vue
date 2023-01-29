<template>
    <EmojiPicker @select="showEmoji" />
    <br />

    <h3>With Input</h3>
    <EmojiPicker picker-type="input" @select="onSelectEmoji" />
    <br />

    <h3>With Textarea</h3>
    <EmojiPicker picker-type="textarea" @select="onSelectEmoji" />
    <br />
    <br />
</template>

<script>
import EmojiPicker from "vue3-emoji-picker";
//import "@/vue3-emoji-picker/dist/style.css";

import { ref } from "vue";
export default {
    components: {
        EmojiPicker: EmojiPicker,
    },
    setup() {
        const input = ref("");

        function onSelectEmoji(emoji) {
            console.log(emoji);
            input.value += emoji.i;
        }

        function showEmoji(emoji) {
            alert(`emoji ${emoji.i} selected, check console for details`);
            console.log(emoji);
        }

        return {
            input,
            onSelectEmoji,
            showEmoji,
        };
    },
};
</script>

<style src="../../../node_modules/vue3-emoji-picker/dist/style.css"></style>
<style>
</style>
