<template>
    <div>
        <component :is="'selene-' + tool.type" :name="name"></component>
    </div>
</template>
<script>
    export default {
        props: {
            name: {
                type: String,
                required: true,
            }
        },
        computed: {
            tool() {
                return this.$store.state.tools[this.name]
            }
        },
    }
</script>