<template>
    <div>
        <template v-if="displayType == 1">
            <small>
                <i class="fa fa-circle text-success" v-if="value"></i>
                <i class="fa fa-circle text-danger" v-else></i>
            </small>
        </template>
        <div class="row" v-if="displayType == 2">
            <div class="col-md-3">
                <strong v-html="field.title"></strong>
            </div>

            <div :class="frameClass">
                <i class="fa fa-circle text-success" v-if="value"></i>
                <i class="fa fa-circle text-danger" v-else></i>
            </div>
        </div>
        <div class="form-group mb-0" v-if="displayType == 3">
            <div class="form-row">
                <div :class="frameClass" class="offset-md-3">
                    <b-form-checkbox :name="field.name"
                                     :state="validationState"
                                     :value="true"
                                     :unchecked-value="false"
                                     v-model="values[field.name]"
                                     @input="clearValidationErrors">
                        <span v-html="field.title"></span>
                    </b-form-checkbox>

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
        methods: {
            onSubmit() {
                if (this.values[this.field.name] == null) {
                    this.$set(this.values, this.field.name, false)
                }
            }
        }
    }
</script>