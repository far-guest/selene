<template>
    <div>
        <component :is="'selene-type-' + fieldData.type" :resource="resource" :model="model" :field="fieldData"
                   :display-type="action"></component>
    </div>
</template>
<script>
    import resourceMixin from '../mixins/resource'

    export default {
        mixins: [resourceMixin],
        props: {
            model: {
                type: [String, Number],
                required: true,
            },
            field: {
                type: String,
                required: true,
            },
            action: {
                type: String,
                required: true,
            }
        },
        computed: {
            fieldData() {
                return this.fields[this.field]
            }
        }
    }
</script>