<template>
    <div>
        <div class="mb-2">
            <b-form-input type="text" size="sm" placeholder="جستجو" v-model="search"></b-form-input>
        </div>
        <div class="mb-2" v-if="fetchComplete && models">
            <b-form-checkbox-group v-model="internalValue" v-if="multiple">
                <div v-for="option in models.data">
                    <b-form-checkbox :value="option.id" @change="selected(option)">
                        <span v-text="option.title"></span>
                    </b-form-checkbox>
                </div>
            </b-form-checkbox-group>
            <b-form-group v-else>
                <div v-for="option in models.data">
                    <b-form-radio v-model="internalValue" :value="option.id" @change="selected(option)">
                        <span v-text="option.title"></span>
                    </b-form-radio>
                </div>
            </b-form-group>
        </div>
        <div class="mb-2" v-else-if="loading">
            <div class="text-center">
                <i class="fa fa-refresh fa-spin fa-2x"></i>
            </div>
        </div>
        <div class="text-center">
            <b-pagination size="sm" class="mb-0" align="center"
                          v-if="models"
                          :disabled="isLoading"
                          @change="pageChanged"
                          :total-rows="models.total"
                          :per-page="models.per_page"
                          v-model="models.current_page">
            </b-pagination>
        </div>
    </div>
</template>

<script>
    import {mapMutations, mapActions} from 'vuex';

    export default {
        model: {
            prop: 'value',
            event: 'input'
        },
        props: {
            lazy: {
                type: Boolean,
                default: false
            },
            lazyEdge: {
                type: Boolean,
                default: false
            },
            url: {
                type: String,
                required: true
            },
            multiple: {
                type: Boolean,
                default: false
            },
            value: {}
        },
        data() {
            return {
                fetchComplete: false,
                lazyLoaded: false,
                internalValue: [],
                search: '',
                models: null,
                refresh_timeout: null,
                loading: false,
            }
        },
        computed: {
            isLoading() {
                return this.$store.state.loading
            },
        },
        watch: {
            search() {
                this.delayedRefreshModels()
            },
            value(val) {
                this.internalValue = val
            },
            internalValue(val) {
                this.$emit('input', val)
            },
            lazyEdge(newVal) {
                if (this.lazy && !this.lazyLoaded && newVal) {
                    this.lazyLoaded = true;
                    this.fetch()
                }
            }
        },
        methods: {
            ...mapMutations([
                'error'
            ]),
            ...mapActions([
                'si',
            ]),
            selected(option) {
                this.$emit('change', option)
            },
            pageChanged() {
                this.$nextTick(() => {
                    this.fetch()
                })
            },
            delayedRefreshModels() {
                this.refresh_timeout == null || clearTimeout(this.refresh_timeout);
                this.refresh_timeout = setTimeout(this.fetch, 1000)
            },
            invalidate() {
                this.models = null;
                this.lazy ? this.lazyEdge ? this.fetch() : this.lazyLoaded = !1 : this.fetch();
            },
            fetch() {
                this.loading = true;
                this.si({
                    url: this.url,
                    data: {
                        page: this.models ? this.models.current_page : 1,
                        search: this.search,
                    },
                    method: 'get'
                }).then(result => {
                    this.fetchComplete = true;
                    this.models = result;
                    this.loading = false;
                }).catch(result => {
                    this.loading = false;
                });
            }
        },
        mounted() {
            if (!this.lazy) {
                this.fetch()
            }
            this.internalValue = this.value
        },
    }
</script>