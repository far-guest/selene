export default {
    props: {
        resource: {},
        field: {
            required: true
        },
        displayType: {
            type: [Number, String],
            default: 1
        },
        validation: {},
        primaryKey: {},
        retrieveUrlGenerator: {
            type: Function,
            default(resource, field) {
                return route('selene.resources.type-retrieve', [this.resource, this.field.name]).withQuery({
                    model: this.values[this.primaryKey]
                });
            }
        },
        model: {
            type: [Number, String],
        },
        values: {
            type: Object,
        },
        noTitle: {
            type: Boolean,
            default: false,
        },
        frameClass: {
            default: 'col-md-9 col-lg-4'
        }
    },
    data() {
        return {
            validationErrors: null,
        }
    },
    computed: {
        value() {
            return this.values[this.field.name]
        },
        validationState() {
            return this.validationErrors == null ? undefined : false
        },
    },
    methods: {
        setValidationErrors(errors) {
            if (errors.hasOwnProperty(this.field.name)) {
                errors = errors[this.field.name];

                this.$nextTick(() => {
                    if (errors != null && typeof errors == 'object') {
                        this.validationErrors = errors.length == 1 ? errors[0] : errors.map(a => '<div>' + a + '</div>').join('');
                    } else {
                        this.validationErrors = errors
                    }
                })
            }
        },
        clearValidationErrors() {
            this.validationErrors = null
        },
    }
}