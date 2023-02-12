export default {
    props: {
        resource: {
            type: String,
            required: true,
        },
    }, computed: {
        res() {
            return this.$store.state.resources[this.resource]
        },
        singular() {
            return this.$store.state.resources[this.resource].singular
        },
        plural() {
            return this.$store.state.resources[this.resource].plural
        },
        createText() {
            return this.$store.state.resources[this.resource].create_text
        },
        panels() {
            return this.$store.state.resources[this.resource].panels
        },
        fields() {
            return _.map(this.$store.state.resources[this.resource].panels, x => x.fields).reduce((carry, item) => _.extend(carry, item), {})
        },
        metrics() {
            return this.$store.state.resources[this.resource].metrics
        },
        actions() {
            return this.$store.state.resources[this.resource].actions
        },
        detailsEnable() {
            return this.$store.state.resources[this.resource].details
        },
        createEnable() {
            return this.$store.state.resources[this.resource].create
        },
        editEnable() {
            return this.$store.state.resources[this.resource].edit
        },
        removeEnable() {
            return this.$store.state.resources[this.resource].remove
        },
        primaryKey() {
            return this.$store.state.resources[this.resource].primary_key
        },
        indexColumn() {
            return this.$store.state.resources[this.resource].index_column
        },
        searchBar() {
            return this.$store.state.resources[this.resource].search
        },
        rearrange() {
            return this.$store.state.resources[this.resource].rearrange
        }
    }
}