<template>
    <div>
        <div class="d-flex justify-content-between">
            <h6 class="text-muted text-ellipsis" v-text="metric.title"></h6>
            <div v-if="hasPeriods">
                <select :disabled="loading" v-model="period">
                    <option :value="index" v-for="(title, index) in metric.periods" v-text="title"></option>
                </select>
            </div>
        </div>

        <h4 class="mt-3 mb-2 px-1">
            <i class="fa fa-refresh fa-spin" v-if="loading"></i>
            <span class="" v-else v-text="value"></span>
        </h4>

        <div v-if="!loading && change">
            <small>
                <i class="fa fa-level-up text-success" v-if="change > 0"></i>
                <i class="fa fa-level-down text-danger" v-else></i>
                <span v-text="change + '%'"></span>
                <span v-if="change > 0">افزایش</span>
                <span v-else>کاهش</span>
            </small>
        </div>
    </div>
</template>
<script>
    import MetricMixin from './../../mixins/metric'

    export default {
        mixins: [MetricMixin,],
        data() {
            return {
                value: null,
                change: null,
                period: null,
            }
        },
        watch: {
            period() {
                this.init()
            }
        },
        computed: {
            hasPeriods() {
                return Object.keys(this.metric.periods).length > 0
            },
            params() {
                return {
                    period: this.period
                }
            },
        },
        methods: {
            onServerResponse(result) {
                this.value = result.value;
                this.change = result.change;
            },
            onServerFail(error) {
                this.value = ":(";
                this.change = null;
            },
        },
        created() {
            if (this.hasPeriods) {
                this.period = Object.keys(this.metric.periods)[0]
            } else {
                this.init()
            }
        }
    }
</script>
