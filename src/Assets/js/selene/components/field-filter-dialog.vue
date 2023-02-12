<template>
    <div @click.stop>
        <button class="btn btn-link btn-sm p-0" tabindex="0"
                :id="'_filter_popover_' + paramPrefix + '_' + field.name">
            <i class="fa fa-filter"></i>
        </button>
        <small v-html="`(${value.length})`"
               v-if="value && value.length > 0"></small>
        <b-popover :target="'_filter_popover_' + paramPrefix + '_' + field.name"
                   :show.sync="pop"
                   container="app"
                   triggers="focus"
                   @hidden="emitValue"
                   placement="bottomright">
            <template slot="title">
                <div class="text-right">
                    <a href="#" class="text-dark"
                       @click.prevent="hideDialog">
                        <i class="fa fa-remove"></i>
                    </a>
                </div>
            </template>

            <selene-relation-selector
                    lazy
                    multiple
                    value-field="id"
                    :lazy-edge="pop"
                    :url="route('selene.resources.type-retrieve', [resource, field.name])"
                    v-model="selected"></selene-relation-selector>

            <div class="text-center mt-1"
                 v-if="value && value.length > 0">
                <small>
                    <a href="javascript:void(0)" class="text-danger"
                       @click="clearAll">
                        <i class="fa fa-remove"></i>
                        <span>حذف فیلتر</span>
                    </a>
                </small>
            </div>
        </b-popover>
    </div>
</template>
<script>
    export default {
        props: {
            resource: {
                required: true,
                type: String
            },
            field: {
                required: true,
                type: Object
            },
            paramPrefix: {
                default: '',
                type: String
            },
            value: {
                type: Array
            }
        },
        data() {
            return {
                selected: [],
                pop: false,
            }
        },
        watch: {
            value(val) {
                this.selected = val
            }
        },
        methods: {
            emitValue() {
                this.$emit('input', this.selected)
            },
            clearAll() {
                this.selected = [];
                this.emitValue();
                this.hideDialog();
            },
            hideDialog() {
                this.$root.$emit('bv::hide::popover', '_filter_popover_' + this.paramPrefix + '_' + this.field.name)
            }
        },
        mounted() {
            this.selected = this.value
        }
    }
</script>