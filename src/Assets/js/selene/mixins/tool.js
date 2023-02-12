export default {
    props: {
        name: {
            type: String,
            required: true,
        }
    },
    computed: {
        tool() {
            return this.$store.state.tools[this.name];
        }
    }
}