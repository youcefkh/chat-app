<template>
    <div>
        <h1>Roulette Game</h1>
        <div
            class="container rounded-5 bg-grey-lighten-3 d-flex justify-content-center align-items-center flex-column"
        >
            <img :class="isRotate ? 'refresh' : ''" :src="img" alt="" />
            <hr>
            <v-select
                v-model="my_bet"
                label="Your bet"
                :items="Array.from(Array(12), (e,i)=>i+1)"
                variant="solo"
                :disabled="timer !== null"
            ></v-select>
            <hr>
            <p class="h5 text-bold">Remaining Time</p>
            <p class="h6 text-red">{{timer ? timer : "Waiting to start"}}</p>
            <hr>
            <p class="h5">Result: <span class="text-purple">{{timer ? null : number}}</span></p>
            <p class="h2 mt-2" v-html="timer ? null : result"></p>
        </div>
    </div>
</template>

<script>
import image from "../../img/wheel.png";
export default {
    data() {
        return {
            img: image,
            isRotate: false,
            timer: null,
            number: null,
            my_bet: 1,
            result: null,
        };
    },
    mounted() {
        Echo.channel('game')
            .listen('RemainingTime', (e) => {
                this.timer = e.time;
                this.isRotate = parseInt(this.timer) !== 0;
            })
            .listen('WinnerNumber', (e) => {
                this.number = e.number;
                this.timer = null;
                this.result = this.my_bet == this.number ? '<span class="text-green">You Win</span>' : '<span class="text-red">You Lose</span>';
            })
    }
};
</script>

<style scoped>
img {
    width: 300px;
    height: 300px;
}
.container {
    height: 80vh;
}
.v-input {
    flex-grow: unset;
    width: 50%;
}
.refresh {
    animation: rotate 1.5s linear infinite;
}

@keyframes rotate {
    from {
        rotate: 0deg;
    }

    to {
        rotate: 360deg;
    }

}
</style>
