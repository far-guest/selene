<template>
    <div>
        <template v-if="displayType == 1">
            <span v-text="value || 0"></span>
        </template>
        <div class="row" v-if="displayType == 2">
            <div class="col-md-3">
                <strong v-html="field.title"></strong>
            </div>

            <div :class="frameClass">
                <span v-text="value || 0"></span>
            </div>
        </div>
        <div class="form-group mb-0" :aria-labelledby="`__input__${field.name}_LBL`" v-if="displayType == 3">
            <div class="form-row">
                <label class="col-md-3 col-form-label"
                       :id="`__input__${field.name}_LBL`"
                       :for="`__input__${field.name}`"
                       v-html="field.title"></label>

                <div :class="frameClass">
                    <input type="number"
                           :name="field.name"
                           :id="`__input__${field.name}`"
                           :placeholder="field.title"
                           class="form-control"
                           :class="{'is-invalid': validationState == false}"
                           v-model="values[field.name]"
                           :step="field.step"
                           @input="clearValidationErrors">

                    <div class="invalid-feedback"
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
