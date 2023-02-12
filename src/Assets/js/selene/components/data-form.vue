<template>
    <div>
        <ajax-form ref="ajaxForm" :custom-submit="true" @submit="onSubmit">
            <div v-for="panel in panels">
                <h5 v-html="panel.name" v-if="!panel.support && panel.name"></h5>
                <div class="card mb-3">
                    <div class="card-header bg-white" v-html="panel.name" v-if="panel.support && panel.name"></div>
                    <div class="card-body py-0">
                        <div class="input-fields">
                            <template v-for="(field, index) in panel.fields">
                                <div class="py-4" :class="{'border-top': index > 0}">
                                    <component :is="'selene-type-' + field.type"
                                               :resource="resource"
                                               :field="field"
                                               :display-type="3"
                                               :primary-key="primaryKey"
                                               :retrieve-url-generator="retrieveUrlGenerator"
                                               :values="values"
                                    ></component>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </ajax-form>
    </div>
</template>
<script>
    export default {
        props: {
            resource: {
                required: true
            },
            panels: {
                required: true,
                type: Array,
            },
            primaryKey: {},
            retrieveUrlGenerator: {
                type: Function,
            },
            values: {
                type: Object,
            },
        },
        methods: {
            submit() {
                this.$refs.ajaxForm.submit()
            },
            onSubmit(data) {
                (new Promise(resolve => {
                    let counter = 0;

                    this.$refs.ajaxForm.eachComponent((component) => {
                        if (component.hasOwnProperty('onSubmit') && typeof component.onSubmit == 'function') {
                            let result = component.onSubmit();
                            if (result instanceof Promise) {
                                counter++;
                                result.then(() => {
                                    counter--;
                                    if (counter == 0) {
                                        resolve()
                                    }
                                });
                            }
                        }
                    });

                    if (counter == 0) {
                        resolve()
                    }
                })).then(() => {
                    this.$emit('submit', data);
                });
            },
            setErrors(errors) {
                this.$refs.ajaxForm.setErrors(errors)
            }
        }
    }
</script>