<template>
    <div>
        <div class="form-group mb-0" :aria-labelledby="`__input__${field.name}_LBL`" v-if="displayType == 3">
            <div class="form-row">
                <label class="col-md-3 col-form-label"
                       :id="`__input__${field.name}_LBL`"
                       :for="`__input__${field.name}`"
                       v-html="field.title"></label>

                <div :class="frameClass">
                    <input type="password"
                           :name="field.name"
                           :id="`__input__${field.name}`"
                           :placeholder="field.title"
                           class="form-control"
                           :class="{'is-invalid': validationState == false}"
                           v-model="values[field.name]"
                           @input="clearValidationErrors">

                    <div class="invalid-feedback"
                         v-html="validationErrors"></div>

                    <input type="password"
                           :name="field.name + '_confirmation'"
                           :id="`__input__${field.name}_confirmation`"
                           :placeholder="'تکرار ' + field.title"
                           class="form-control mt-3"
                           v-model="values[field.name + '_confirmation']"
                           @input="clearValidationErrors">
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