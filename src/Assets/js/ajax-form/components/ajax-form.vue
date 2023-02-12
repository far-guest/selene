<template>
    <form ref="form" :action="action" :method="method" @submit.prevent="submit">
        <slot></slot>
    </form>
</template>
<script>
    import {mapActions} from 'vuex';

    export default {
        props: {
            method: {
                default: 'post'
            },
            action: {
                default: ''
            },
            customSubmit: {
                type: Boolean,
                default: false
            }
        },
        computed: {
            isLoading() {
                return this.$store.state.loading
            },
        },
        methods: {
            ...mapActions([
                'error',
                'si',
            ]),
            collectDate() {
                return _.reduce($(this.$refs.form).serializeArray(), (carry, item) => {
                    if (carry.hasOwnProperty(item.name)) {
                        if (typeof carry[item.name] == 'string') {
                            carry[item.name] = [carry[item.name]];
                        }

                        carry[item.name].push(item.value)
                    } else {
                        carry[item.name] = item.value;
                    }

                    return carry
                }, {});
            },
            submit() {
                if (!this.isLoading) {
                    this.resetErrors();
                    let data = this.collectDate();

                    this.$emit('submit', data);

                    if (!this.customSubmit) {
                        this.si({
                            url: this.action,
                            method: this.method,
                            data: data,
                        })
                            .then(this.serverSuccess)
                            .catch(this.serverFailed)
                    }
                }
            },
            serverSuccess(result) {
                this.$emit('success', result)
            },
            serverFailed(result) {
                if (result.hasOwnProperty('response') && result.response.status == 422) {
                    if (result.response.hasOwnProperty('data')) {
                        if (result.response.data.hasOwnProperty('message'))
                            this.error({
                                title: 'خطا',
                                text: result.response.data.message,
                            });

                        if (result.response.data.hasOwnProperty('errors'))
                            this.setErrors(result.response.data.errors)
                    }
                }
            },
            setErrors(errors) {
                this.eachComponent((component) => {
                    if (component._props && component._props.hasOwnProperty('field') && typeof component.setValidationErrors == 'function') {
                        component.setValidationErrors(errors)
                    }
                });
            },
            resetErrors() {
                this.eachComponent((component) => {
                    if (component._props && component._props.hasOwnProperty('field') && typeof component.clearValidationErrors == 'function') {
                        component.clearValidationErrors()
                    }
                });
            },
            eachComponent(fn, children) {
                if (children == undefined) {
                    this.eachComponent(fn, this.$children);
                }

                for (let x in children) {
                    fn(children[x]);

                    if (children[x].$children.length > 0) {
                        this.eachComponent(fn, children[x].$children);
                    }
                }
            },
        },
    }
</script>
