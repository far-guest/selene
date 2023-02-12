<template>
    <div>
        <slot :errors="validationErrors" :state="validationErrors == null ? undefined : false" :clear="clear"></slot>
    </div>
</template>
<script>
    export default {
        props: {
            name: {
                required: true
            }
        },
        data() {
            return {
                validationErrors: null,
            }
        },
        methods: {
            setValidationErrors(errors) {
                this.$nextTick(() => {
                    if (errors != null && typeof errors == 'object') {
                        this.validationErrors = errors.length == 1 ? errors[0] : errors.map(a => '<div>' + a + '</div>').join('');
                    } else {
                        this.validationErrors = errors
                    }
                })
            },
            clear() {
                this.setValidationErrors(null)
            }
        },
    }
</script>