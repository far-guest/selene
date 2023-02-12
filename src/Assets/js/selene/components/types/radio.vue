<template>
    <div>
        <template v-if="displayType == 1">
            <small>
                <span class="mx-1" v-for="(val, key) in field.options">
                    <i class="fa fa-circle text-success" v-if="values[field.name] == key"></i>
                    <i class="fa fa-circle text-danger" v-else></i>
                </span>
            </small>
        </template>
        <div class="row" v-if="displayType == 2">
            <div class="col-md-3">
                <strong v-html="field.title"></strong>
            </div>

            <div :class="frameClass">
                <span v-text="field.options[values[field.name]]"
                      v-if="field.options.hasOwnProperty(values[field.name])"></span>
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
                    <div class="custom-control custom-radio" v-for="(value, key) in field.options">
                        <input type="radio" :id="`__input__${field.name}_${key}`" :name="`__input__${field.name}`"
                               class="custom-control-input" :value="key" v-model="values[field.name]"
                               @change="clearValidationErrors">
                        <label class="custom-control-label" :for="`__input__${field.name}_${key}`"
                               v-text="value"></label>
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

    export default {
        mixins: [seleneType],
    }
</script>