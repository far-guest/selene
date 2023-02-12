import {mapActions} from 'vuex';

export default {
    props: {
        metric: {
            type: Object,
            required: true,
        },
        index: {
            type: Number,
            required: true,
        },
        url: {
            required: true,
        }
    },
    data() {
        return {
            loading: false,
        }
    },
    methods: {
        ...mapActions([
            'si',
        ]),
        init() {
            this.loading = true;

            this.si({
                url: this.url,
                method: 'get',
                data: this.params
            }).then((result) => {
                this.loading = false;

                if (this.onServerResponse) {
                    this.onServerResponse(result)
                }
            }).catch((error) => {
                this.loading = false;

                if (this.onServerFail) {
                    this.onServerFail(error)
                }
            });
        },
    },
}