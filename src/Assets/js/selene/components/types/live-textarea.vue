<template>
    <div>
        <template v-if="displayType == 1">
            <span v-text="value" v-if="value"></span>
            <slot name="empty" v-else></slot>
        </template>
        <div class="row" v-if="displayType == 2">
            <div class="col-md-3">
                <label class="col-form-label"
                       :id="`__input__${field.name}_LBL`"
                       :for="`__input__${field.name}`"
                       v-html="field.title"></label>
                <i class="fa fa-refresh fa-spin" v-if="loading"></i>
            </div>

            <div :class="frameClass">
                <textarea :name="field.name"
                          rows="4"
                          @input="save"
                          class="form-control"
                          :placeholder="field.title"
                          v-model="values[field.name]"
                          :id="`__input__${field.name}`"></textarea>
            </div>
        </div>
        <div class="form-group mb-0" :aria-labelledby="`__input__${field.name}_LBL`" v-if="displayType == 3">
            <div class="form-row">
                <label class="col-md-3 col-form-label"
                       :id="`__input__${field.name}_LBL`"
                       :for="`__input__${field.name}`"
                       v-html="field.title"></label>

                <div :class="frameClass">
                    <textarea :name="field.name"
                              rows="4"
                              class="form-control"
                              :placeholder="field.title"
                              v-model="values[field.name]"
                              @input="clearValidationErrors"
                              :id="`__input__${field.name}`"
                              :class="{'is-invalid': validationState == false}"></textarea>

                    <div class="invalid-feedback" v-html="validationErrors"></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import seleneType from '../../mixins/input'
    import {mapActions} from 'vuex';

    export default {
        mixins: [seleneType],
        data() {
            return {
                toggle: true,
                loading: false,
                saveTimeout: null,
            }
        },
        methods: {
            ...mapActions([
                'si',
            ]),
            save() {
                this.saveTimeout == null || clearTimeout(this.saveTimeout);
                this.saveTimeout = setTimeout(() => {
                    this.loading = true;
                    let d = {};
                    d[this.field.name] = this.value;
                    this.si({
                        url: route('selene.resources.type-action', [this.resource, this.values[this.primaryKey], this.field.name]),
                        method: 'post',
                        data: d
                    }).then(() => {
                        this.loading = false
                    }).catch(() => {
                        this.loading = false
                    })
                }, 1000);
            }
        }
    }
</script>