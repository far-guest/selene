<template>
    <div>
        <template v-if="displayType == 1">
            <template v-if="value && value.length > 0">
                <template v-for="item, i in value">
                    <strong v-if="i > 0">,</strong>
                    <a href="javascript:void(0)" v-text="item.title"
                       @click="$router.push({name:'resources.details', params:{resource:field.relation_resource, model:item.id}})"></a>
                </template>
            </template>
            <slot name="empty" v-else></slot>
        </template>
        <div class="row" v-if="displayType == 2">
            <div class="col-md-3">
                <strong v-html="field.title"></strong>
            </div>

            <div class="col-md-9">
                <div v-if="value && value.length > 0">
                    <ul class="m-0 p-0">
                        <li v-for="item in value">
                            <a href="javascript:void(0)" v-text="item.title"
                               @click="$router.push({name:'resources.details', params:{resource:field.relation_resource, model:item.id}})"></a>
                        </li>
                    </ul>
                </div>
                <slot name="empty" v-else></slot>
            </div>
        </div>
        <div class="form-group mb-0" :aria-labelledby="`__input__${field.name}_LBL`" v-if="displayType == 3">
            <div class="form-row">
                <label class="col-md-3 col-form-label"
                       :id="`__input__${field.name}_LBL`"
                       :for="`__input__${field.name}`"
                       v-html="field.title"></label>

                <div class="col-md-9 col-lg-5">
                    <div dir="rtl">
                        <vue-multiselect :options="options || []" v-model="values[field.name]" multiple searchable
                                         :close-on-select="false" :placeholder="field.title" track-by="id" label="title"
                                         :loading="loading"></vue-multiselect>
                    </div>

                    <div class="invalid-feedback"
                         :class="{'d-block': validationErrors != null}"
                         v-html="validationErrors"></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import seleneType from '../../mixins/input'
    import {mapActions} from 'vuex';
    import vueMultiSelect from 'vue-multiselect'

    export default {
        mixins: [seleneType],
        components: {'vue-multiselect': vueMultiSelect},
        data() {
            return {
                loading: false,
                options: null
            }
        },
        computed: {
            retrieveUrl() {
                return this.retrieveUrlGenerator(this.resource, this.field.name).withQuery(
                    this.field.dependencies ?
                        Object.keys(this.values)
                            .filter(key => this.field.dependencies.includes(key))
                            .reduce((acc, key) => (acc[`dependencies[${key}]`] = JSON.stringify(this.values[key]), acc), {})
                        : {}
                ).url()
            },
        },
        watch: {
            value() {
                if (this.validationErrors)
                    this.clearValidationErrors()
            },
            retrieveUrl() {
                if (this.displayType == 3) {
                    this.invalidate()
                }
            }
        },
        methods: {
            ...mapActions([
                'si',
            ]),
            fetchOptions() {
                this.loading = true;

                this.si({url: this.retrieveUrl}).then(result => {
                    this.loading = false;
                    this.options = result
                })
            },
            invalidate() {
                this.fetchOptions();
                this.$set(this.values, this.field.name, null)
            }
        },
        created() {
            if (this.displayType == 3) {
                this.fetchOptions()
            }
        }
    }
</script>
<style>
    @import "~vue-multiselect/dist/vue-multiselect.min.css";

    .multiselect__placeholder {
        padding-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .multiselect__tag {
        margin-right: 0 !important;
        margin-left: 10px !important;
        margin-bottom: 0 !important;
    }
</style>