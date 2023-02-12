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
          <div v-text="value" v-if="toggle"></div>
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
                <textarea :name="field.name"
                          :id="`__input__${field.name}`"
                          :placeholder="field.title"
                          class="form-control"
                          :class="{'is-invalid': validationState == false}"
                          rows="4"
                          v-model="values[field.name]"
                          @input="clearValidationErrors"></textarea>

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
  data() {
    return {
      toggle: true
    }
  }
}
</script>