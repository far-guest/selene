<template>
  <div>
    <div class="d-flex justify-content-start align-items-center mb-3">
      <div v-if="backEnable">
        <button @click="$router.go(-1)" class="btn btn-link">
          <i class="fa fa-arrow-right"></i>
        </button>
        <span class="text-muted mr-3">/</span>
      </div>
      <h4 class="mb-0">
        <span v-text="(model == null ? 'ایجاد' : 'ویرایش') + ' ' + singular"></span>
        <a :href="res.create_help_link" target="_blank" class="text-muted" v-if="model == null && res.create_help_link">
          <small><i class="fa fa-question-circle"></i></small>
        </a>
        <a :href="res.edit_help_link" target="_blank" class="text-muted" v-if="model != null && res.edit_help_link">
          <small><i class="fa fa-question-circle"></i></small>
        </a>
      </h4>
    </div>

    <div v-if="ready">
      <selene-data-form ref="dataForm"
                      :values="values"
                      :resource="resource"
                      :panels="visiblePanels"
                      :primary-key="primaryKey"
                      @submit="submit">
      </selene-data-form>

      <div class="mb-3">
        <div class="d-flex justify-content-end">
          <button class="btn btn-primary" @click="doSubmitAndReset" :disabled="isLoading">
            <span v-text="(model == null ? 'ایجاد' : 'ویرایش') + ' و ماندن در صفحه'"></span>
          </button>
          <button class="btn btn-primary ml-2" @click="doSubmit" :disabled="isLoading">
            <span v-html="(model == null ? 'ایجاد' : 'ویرایش') + ' ' + singular"></span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import {mapActions} from 'vuex';
import resourceMixin from '../mixins/resource'

export default {
  mixins: [resourceMixin],
  props: {
    model: {
      type: [String, Number],
      default: null
    },
    backEnable: {
      type: Boolean,
      default: true
    },
  },
  data() {
    return {
      ready: false,
      values: {},
      goBack: false,
    }
  },
  computed: {
    isLoading() {
      return this.$store.state.loading
    },
    visibleFields() {
      return _.filter(this.fields, this.isVisible)
    },
    visiblePanels() {
      return _.reduce(this.panels, this.isVisiblePanel, [])
    },
    forcedFields() {
      return this.$route.query
    }
  },
  methods: {
    ...mapActions([
      'error',
      'si',
    ]),
    isVisible(field) {
      return this.model == null ? field.isVisibleWhenCreating : field.isVisibleWhenEditing
    },
    isVisiblePanel(carry, field) {
      let fields = _.filter(field.fields, x => this.isVisible(x));

      if (fields.length > 0) {
        let clone = _.clone(field, true);
        clone.fields = fields;
        carry.push(clone);
      }

      return carry
    },
    doSubmit() {
      if (!this.isLoading) {
        this.goBack = true;
        this.$refs.dataForm.submit();
      }
    },
    doSubmitAndReset() {
      if (!this.isLoading) {
        this.goBack = false;
        this.$refs.dataForm.submit();
      }
    },
    submit() {
      if (!this.isLoading) {
        let data = this.values, url;

        url = this.model == null ? route('selene.resources.create', this.resource) : route('selene.resources.update', [this.resource, this.model]);

        this.si({url: url, method: 'post', data: data})
            .then(result => {
              if (this.goBack) {
                this.$router.go(-1);
              } else {
                this.model ? this.fetchAndInit() : this.init()
              }
            })
            .catch(result => {
              if (result.hasOwnProperty('response') && result.response.status == 422) {
                if (result.response.hasOwnProperty('data')) {
                  if (result.response.data.hasOwnProperty('message'))
                    this.error({
                      title: 'خطا',
                      text: result.response.data.message,
                    });

                  if (result.response.data.hasOwnProperty('errors'))
                    this.$refs.dataForm.setErrors(result.response.data.errors)
                }
              }
            })
      }
    },
    fetchAndInit() {
      this.ready = false;
      this.si({
        url: this.route('selene.resources.edit', [this.resource, this.model]),
        method: 'get'
      }).then(result => {
        this.init(result);
        this.ready = true;
      })
    },
    init(values) {
      this.ready = false;
      typeof values == 'object' || (values = {});

      this.values = _.reduce(values, (carry, item, index) => {
        carry[index] = item;
        return carry;
      }, {});

      for (let f of this.visibleFields) {
        if (!this.values.hasOwnProperty(f.name) && f.default !== null) {
          this.$set(this.values, f.name, f.default)
        }
      }

      for (let field in this.forcedFields) {
        this.$set(this.values, field, Number(this.forcedFields[field]))
      }

      this.$nextTick(() => {
        this.ready = true
      })
    },
  },
  created() {
    if (this.model == null) {
      this.init();
    } else {
      this.fetchAndInit()
    }
  }
}
</script>
