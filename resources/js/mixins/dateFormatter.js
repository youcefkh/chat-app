import moment from "moment";
export default {
    data() {
        return {
            moment: moment,
        };
    },
    methods: {
        formatDate(date) {
            const dateFormated = this.moment(date).format("YYYY-MM-DD");
            const oneDayAgo = this.moment()
                .subtract(1, "days")
                .format("YYYY-MM-DD");
            const twoDaysAgo = this.moment()
                .subtract(2, "days")
                .format("YYYY-MM-DD");

            const isAfterOneDay = this.moment(dateFormated).isSameOrAfter(oneDayAgo);
            const isAfterTwoDays = this.moment(dateFormated).isSameOrAfter(twoDaysAgo);

            if (isAfterOneDay) {
                return this.moment(date).format("LT");

            } else if (isAfterTwoDays) {
                return this.moment(date).fromNow();

            } else {
                return this.moment(date).format("MMM Do");
                
            }
        },
    },
};
