<template>
  <div>
    <template v-if="displayType == 1">
      <template v-if="value">
        <a href="javascript:void(0)" v-text="value.title" v-if="field.link"
           @click="$router.push({name:'resources.details', params:{resource:field.relation_resource, model:value.id}})"></a>
        <span v-else v-text="value.title"></span>
      </template>
      <slot name="empty" v-else></slot>
    </template>
    <div class="row" v-if="displayType == 2">
      <div class="col-md-3">
        <strong v-html="field.title"></strong>
      </div>

      <div :class="frameClass">
        <template v-if="value">
          <a href="javascript:void(0)" v-text="value.title" v-if="field.link"
             @click="$router.push({name:'resources.details', params:{resource:field.relation_resource, model:value.id}})"></a>
          <span v-else v-text="value.title"></span>
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
          <a href="javascript:void(0)"
             :id="'_relation_popover_' + field.name">
                        <span v-if="loading">
                            <i class="fa fa-refresh fa-spin"></i>
                        </span>
            <span v-text="selected.title" v-else-if="selected"></span>
            <span v-else>انتخاب کنید</span>
          </a>

          <b-popover :target="'_relation_popover_' + field.name" :show.sync="pop"
                     container="app" placement="bottomleft">

            <template slot="title">
              <div class="text-right">
                <a href="#" class="text-dark"
                   @click.prevent="$root.$emit('bv::hide::popover', '_relation_popover_' + field.name)">
                  <i class="fa fa-remove"></i>
                </a>
              </div>
            </template>

            <selene-relation-selector
                ref="relationSelector"
                lazy
                :lazy-edge="pop"
                :url="relationSelectorUrl"
                @change="setSelectedOption"
                v-model="values[field.name]"></selene-relation-selector>

          </b-popover>

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
import {mapActions} from 'vuex';

export default {
  mixins: [seleneType],
  data() {
    return {
      pop: false,
      loading: false,
      selected: null,
    }
  },
  computed: {
    relationSelectorUrl() {
      return this.retrieveUrlGenerator(this.resource, this.field.name).withQuery(
          this.field.dependencies ?
              Object.keys(this.values)
                  .filter(key => this.field.dependencies.includes(key))
                  .reduce((acc, key) => (acc[`dependencies[${key}]`] = JSON.stringify(this.values[key]), acc), {})
              : {}
      ).url()
    }
  },
  watch: {
    value() {
      if (this.validationErrors)
        this.clearValidationErrors()
    },
    relationSelectorUrl(n, o) {
      if (this.displayType == 3) {
        this.invalidate()
      }
    }
  },
  methods: {
    ...mapActions([
      'si',
    ]),
    setSelectedOption(option) {
      this.loading = false;
      this.selected = option;

      if (!option) {
        this.$set(this.values, this.field.name, null)
      }
    },
    fetchSelected(id) {
      this.loading = true;

      this.si({
        url: this.retrieveUrlGenerator(this.resource, this.field.name).withQuery({
          lookup: id
        }),
      }).then(result => {
        this.loading = false;

        if (!this.selected) {
          this.selected = result
        }
      })
    },
    invalidate() {
      this.$refs.relationSelector.invalidate();
      this.setSelectedOption(null)
    }
  },
  created() {
    if (this.displayType == 3 && this.values[this.field.name]) {
      this.fetchSelected(this.values[this.field.name])
    }
  }
}
</script>