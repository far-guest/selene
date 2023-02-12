<template>
    <div>
        <template v-if="displayType == 1">
            <span v-text="value" v-if="value"></span>
            <slot name="empty" v-else></slot>
        </template>
        <div class="row" v-if="displayType == 2">
            <div class="col-md-3">
                <strong v-html="field.title"></strong>
            </div>

            <div :class="frameClass">
                <div v-if="value">
                    <p>
                        <a href="javascript:void(0)" @click="toggle = !toggle">
                            <span v-if="toggle">مخفی کردن</span>
                            <span v-else>نمایش محتوا</span>
                        </a>
                    </p>
                    <div class="bg-white border rounded p-2" v-if="toggle">
                        <div v-html="value"></div>
                    </div>
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

                <div :class="frameClass">
                    <div id="editor">
                        <ckeditor :id="`__input__${field.name}`" v-model="values[field.name]" :toolbar="field.toolbars"
                                  @input="clearValidationErrors"></ckeditor>
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
    import ckeditor from '../helpers/editor'

    export default {
        mixins: [seleneType],
        components: {ckeditor},
        data() {
            return {
                toggle: true,
            }
        }
    }
</script>
