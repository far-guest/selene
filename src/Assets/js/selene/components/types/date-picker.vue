<template>
  <div>
    <template v-if="displayType == 1">
      <span v-text="dv" v-if="dv"></span>
      <slot name="empty" v-else></slot>
    </template>
    <div class="row" v-if="displayType == 2">
      <div class="col-md-3">
        <strong v-html="field.title"></strong>
      </div>

      <div :class="frameClass">
        <span v-text="dv" v-if="dv"></span>
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
          <date-picker
              v-model="values[field.name]"
              :format="field.format"
              :display-format="field.display_format"
              :type="field.v_type"
              :locale="field.locale"
              @change="clearValidationErrors"
          />

          <div class="invalid-feedback d-block mt-3" v-if="validationState === false"
               v-html="validationErrors"></div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import seleneType from '../../mixins/input'
import {mapActions} from 'vuex'
import moment from 'moment-jalaali'

export default {
  mixins: [seleneType],
  computed: {
    dv() {
      return this.value ? moment(this.value, this.field.format).format(this.field.display_format) : ''
    }
  },
  methods: {
    ...mapActions([
      'si',
    ]),
  }
}
</script>