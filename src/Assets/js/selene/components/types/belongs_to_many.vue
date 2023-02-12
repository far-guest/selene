<template>
  <div>
    <div class="pt-3" v-if="displayType == 2">
      <selene-metric-bar :metrics="field.metrics" :url-generator="metricUrlGenerator"></selene-metric-bar>

      <h4 v-text="field.title" class="mb-3" v-if="!noTitle"></h4>

      <selene-data-table ref="dataTable"
                       :resource="rResource"
                       :singular="rSingular"
                       :plural="rPlural"
                       :param-prefix="`${field.name}_`"
                       :fields="visibleFieldsOnIndex"
                       :create-enable="field.attach"
                       :create-text="field.create_text || `پیوست ${rSingular}`"
                       :details-enable="false"
                       :edit-enable="field.edit"
                       :remove-enable="field.detach"
                       :primary-key="rPrimaryKey"
                       :fetch-url="fetchUrl"
                       :index-column="field.index_column"
                       :search-bar="field.search"
                       @create="create"
                       @details="details"
                       @edit="edit"
                       @remove="remove">
      </selene-data-table>
    </div>
    <div v-if="displayType == 'create' || displayType == 'edit'">
      <div class="d-flex justify-content-start align-items-center mb-3">
        <div v-if="backEnable">
          <button @click="$router.go(-1)" class="btn btn-link">
            <i class="fa fa-arrow-right"></i>
          </button>
          <span class="text-muted mr-3">/</span>
        </div>
        <h4 v-text="(editingModel == null ? 'پیوست' : 'ویرایش') + ' ' + rSingular" class="mb-0"></h4>
      </div>

      <div v-if="ready">
        <selene-data-form ref="dataForm"
                        :values="fValues"
                        :resource="rResource"
                        :panels="visiblePanelsOnForm"
                        :primary-key="rPrimaryKey"
                        :retrieve-url-generator="urlGenerator"
                        @submit="submit">
        </selene-data-form>

        <div class="mb-3">
          <div class="d-flex justify-content-end">
            <button class="btn btn-primary" @click="doSubmitAndReset" :disabled="isLoading">
              <span v-text="(editingModel == null ? 'پیوست' : 'ویرایش') + ' و ماندن در صفحه'"></span>
            </button>
            <button class="btn btn-primary ml-2" @click="doSubmit" :disabled="isLoading">
              <span v-html="(editingModel == null ? 'پیوست' : 'ویرایش') + ' ' + rSingular"></span>
            </button>
          </div>
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
  props: {
    backEnable: {
      default: true,
      type: Boolean
    }
  },
  computed: {
    isLoading() {
      return this.$store.state.loading
    },
    rResource() {
      return this.field.relation_resource
    },
    rSingular() {
      return this.$store.state.resources[this.rResource].singular
    },
    rPlural() {
      return this.$store.state.resources[this.rResource].plural
    },
    rPanels() {
      return this.field.panels
    },
    rFields() {
      return _.map(this.field.panels, x => x.fields).reduce((carry, item) => _.extend(carry, item), {})
    },
    rPrimaryKey() {
      return this.field.primaryKey
    },
    fetchUrl() {
      return route('selene.resources.type-retrieve', [this.resource, this.field.name]).withQuery({
        action: 'index',
        model: this.values[this.primaryKey],
      })
    },
    editingModel() {
      return this.$route.query.item
    },
    visibleFieldsOnIndex() {
      return _.filter(this.rFields, x => x.isVisibleOnIndex)
    },
    visiblePanelsOnForm() {
      let panels = _.reduce(this.rPanels, this.isVisiblePanelOnForm, []);

      panels.unshift({
        name: null,
        support: true,
        fields: {
          _: {
            name: '_',
            title: this.rSingular,
            type: 'belongs_to',
            relation: this.rResource,
          }
        }
      });

      return panels
    }
  },
  data() {
    return {
      ready: false,
      fValues: {},
      goBack: false,
    }
  },
  methods: {
    ...mapActions([
      'error',
      'si',
    ]),

    metricUrlGenerator(index) {
      return this.route('selene.resources.type-retrieve', [this.resource, this.field.name]).withQuery({
        action: 'metric',
        model: this.values[this.primaryKey],
        metric: index,
      });
    },

    urlGenerator(resource, field) {
      return route('selene.resources.type-retrieve', [this.resource, this.field.name]).withQuery({
        field: field,
        model: this.model,
      })
    },

    /* ************** index methods ************** */

    create() {
      this.$router.push({
        name: 'resources.type-action',
        params: {
          resource: this.resource,
          model: this.values[this.primaryKey],
          field: this.field.name,
          action: 'create'
        }
      })
    },
    details(id) {
      this.$router.push({
        name: 'resources.type-action',
        params: {
          resource: this.resource,
          model: this.values[this.primaryKey],
          field: this.field.name,
          action: 'details',
        },
        query: {
          item: id,
        }
      })
    },
    edit(id) {
      this.$router.push({
        name: 'resources.type-action',
        params: {
          resource: this.resource,
          model: this.values[this.primaryKey],
          field: this.field.name,
          action: 'edit',
        },
        query: {
          item: id,
        }
      })
    },
    remove(id) {
      this.$swal({
        title: 'آیا مطمئن هستید؟',
        text: "این عملیات قابل برگشت نیست!",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله، حذف کنید!',
        cancelButtonText: 'انصراف',
        target: document.getElementById('app')
      }).then((result) => {
        if (result.value) {
          this.si({
            url: route('selene.resources.type-action', [this.resource, this.values[this.primaryKey], this.field.name]).withQuery({
              action: 'detach',
              item: id
            }),
            method: 'post'
          }).then(this.$refs['dataTable'].fetch)
        }
      })
    },

    /* ************** form methods ************** */

    isVisibleOnForm(field) {
      return this.editingModel == null ? field.isVisibleWhenCreating : field.isVisibleWhenEditing
    },
    isVisiblePanelOnForm(carry, field) {
      let fields = _.filter(field.fields, x => this.isVisibleOnForm(x));

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
        let data = this.fValues, url;

        if (this.editingModel == null) {
          url = route('selene.resources.type-action', [this.resource, this.model, this.field.name]).withQuery({
            action: 'attach',
          })
        } else {
          url = route('selene.resources.type-action', [this.resource, this.model, this.field.name]).withQuery({
            action: 'update',
            item: this.editingModel
          })
        }

        this.si({url: url, method: 'post', data: data})
            .then(result => {
              if (this.goBack) {
                this.$router.go(-1);
              } else {
                this.editingModel == null ? this.init() : this.fetchAndInit()
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
        url: route('selene.resources.type-action', [this.resource, this.model, this.field.name]).withQuery({
          action: 'edit',
          item: this.editingModel
        }),
        method: 'get'
      }).then(result => {
        this.init(result);
        this.ready = true;
      })
    },
    init(model) {
      this.ready = false;
      typeof model == 'object' || (model = {});

      this.fValues = _.reduce(model, (carry, item, index) => {
        carry[index] = item;
        return carry;
      }, {});

      this.$nextTick(() => {
        this.ready = true
      })
    },
  },
  created() {
    if (this.editingModel == null) {
      this.init();
    } else {
      this.fetchAndInit()
    }
  }
}
</script>