<template>
    <component :is="'selene-metric-' + metric.type" :metric="metric" :index="index" :url="url(index)"></component>
</template>

<script>
    import ValueMetric from './value'

    export default {
        components: {
            'selene-metric-value': ValueMetric,
        },
        props: {
            metric: {
                type: Object,
                required: true,
            },
            index: {
                type: Number,
                required: true,
            },
            urlGenerator: {
                type: Function,
                required: true,
            }
        },
        methods: {
            url(index) {
                return this.urlGenerator(index)
            }
        }
    }
</script>