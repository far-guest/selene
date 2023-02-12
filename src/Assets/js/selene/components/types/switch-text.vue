<template>
    <div>
        <template v-if="displayType == 1">
            <span v-text="value" v-if="value"></span>
            <i class="fa fa-circle text-danger" v-else></i>
        </template>
        <div class="row" v-if="displayType == 2">
            <div class="col-md-3">
                <strong v-html="field.title"></strong>
            </div>

            <div :class="frameClass">
                <span v-text="value" v-if="value"></span>
                <slot name="empty" v-else></slot>
            </div>
        </div>
        <div class="form-group mb-0" :aria-labelledby="`__input__${field.name}_LBL`" v-if="displayType == 3">
            <div class="form-row">
                <label class="col-md-3 col-form-label"
                       :id="`__input__${field.name}_LBL`"
                       :for="`__input__${field.name}`"
                       v-html="field.title"></label>

                <div :class="frameClass">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <label class="input-group-text">
                                <input type="checkbox" :checked="value != null" @input="checked">
                            </label>
                        </div>

                        <input type="text"
                               :name="field.name"
                               :id="`__input__${field.name}`"
                               class="form-control"
                               :class="{'is-invalid': validationState == false}"
                               :disabled="value == null"
                               v-model="values[field.name]"
                               @input="clearValidationErrors">
                    </div>

                    <div class="invalid-feedback" :class="{'d-block': validationErrors}"
                         v-html="validationErrors"></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import seleneType from '../../mixins/input'

    export default {
        mixins: [seleneType],
        methods: {
            checked(event) {
                this.$set(this.values, this.field.name, event.target.checked ? this.field.default: null)
            }
        }
    }
</script>