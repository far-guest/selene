<template>
  <div>
    <template v-if="displayType == 1">
      <small>
                <span class="mx-1" v-for="(val, key) in field.options">
                    <i class="fa fa-circle text-success"
                       v-if="(field.multiple && values[field.name] && values[field.name].includes(key)) || (values[field.name] == key)"></i>
                    <i class="fa fa-circle text-danger" v-else></i>
                </span>
      </small>
    </template>
    <div class="row" v-if="displayType == 2">
      <div class="col-md-3">
        <strong v-html="field.title"></strong>
      </div>

      <div :class="frameClass">
        <template v-if="field.multiple">
          <div v-for="v in values[field.name]">
            <span v-text="field.options[v]"
                  v-if="field.options.hasOwnProperty(v)"></span>
          </div>
        </template>
        <template v-else>
          <span v-text="field.options[values[field.name]]"
                v-if="field.options.hasOwnProperty(values[field.name])"></span>
        </template>

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
          <template v-if="field.multiple">
            <b-form-checkbox-group
                id="`__input__${field.name}`"
                v-model="values[field.name]"
                :options="optionsObject"
                :name="field.name"
                @change="clearValidationErrors"
                stacked
            >
            </b-form-checkbox-group>
          </template>
          <template v-else>
            <select class="custom-select"
                    :class="{'is-invalid': validationState == false}"
                    :id="`__input__${field.name}`"
                    :name="field.name"
                    v-model="values[field.name]"
                    @change="clearValidationErrors">
              <option :value="null"></option>
              <option v-for="(value, key) in field.options" :value="key" v-text="value"></option>
            </select>
          </template>
          <div class="invalid-feedback d-block mt-3" v-if="validationState === false"
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
  computed: {
    optionsObject() {
      return _.reduce(this.field.options, (carry, item, index) => {
        carry.push({text: item, value: index})
        return carry
      }, [])
    }
  },
  mounted() {
    if (this.field.multiple && !Array.isArray(this.value)) {
      this.$set(this.values, this.field.name, [])
    }
  }
}
</script>