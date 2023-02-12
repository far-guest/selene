<template>
  <div class="pt-3" v-if="displayType == 2">
    <rearrange :retrieve-url="rearrangeUrl" ref="rearrange" @done="doneRearrange"/>

    <selene-metric-bar :metrics="field.metrics" :url-generator="metricUrlGenerator"></selene-metric-bar>

    <h4 v-text="field.title" class="mb-3" v-if="!noTitle"></h4>

    <selene-data-table ref="dataTable"
                     :resource="rResource"
                     :singular="rSingular"
                     :plural="rPlural"
                     :param-prefix="`${field.name}_`"
                     :fields="visibleFields"
                     :create-enable="field.create"
                     :create-text="field.create_text || `ایجاد ${rSingular}`"
                     :details-enable="field.details"
                     :edit-enable="field.edit"
                     :remove-enable="field.delete"
                     :primary-key="rPrimaryKey"
                     :fetch-url="fetchUrl"
                     :index-column="field.index_column"
                     :search-bar="field.search"
                     :rearrange="field.rearrange"
                     @rearrange="$refs.rearrange.show()"
                     @create="create"
                     @details="details"
                     @edit="edit"
                     @remove="remove">
    </selene-data-table>
  </div>
</template>
<script>
import {mapActions} from 'vuex';
import seleneType from '../../mixins/input'
import Rearrange from "../rearrange";

export default {
  components: {Rearrange},
  mixins: [seleneType],
  computed: {
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
    visibleFields() {
      return _.filter(this.rFields, x => x.isVisibleOnIndex)
    },
    visiblePanels() {
      return _.reduce(this.panels, this.isVisiblePanel, [])
    },
    rearrangeUrl() {
      return route('selene.resources.type-action', [this.resource, this.values[this.primaryKey], this.field.name]).withQuery({
        action: 'rearrange',
      })
    },
  },
  methods: {
    ...mapActions([
      'si',
    ]),
    metricUrlGenerator(index) {
      return this.route('selene.resources.type-action', [this.resource, this.field.name]).withQuery({
        action: 'metric',
        model: this.values[this.primaryKey],
        metric: index,
      });
    },
    create() {
      let query = {};
      query[this.field.foreign] = this.values[this.primaryKey];
      if (this.field.dependencies) {
        query = Object.keys(this.values)
            .filter(key => this.field.dependencies.includes(key))
            .reduce((acc, key) => (acc[key] = (typeof this.values[key] == 'object' ? this.values[key].id : this.values[key]), acc), query)
      }
      this.$router.push({name: 'resources.create', params: {resource: this.rResource}, query: query})
    },
    details(id) {
      this.$router.push({name: 'resources.details', params: {resource: this.rResource, model: id}})
    },
    edit(id) {
      this.$router.push({name: 'resources.edit', params: {resource: this.rResource, model: id}})
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
            url: this.route('selene.resources.destroy', [this.rResource, id]),
            method: 'post'
          }).then(this.$refs['dataTable'].fetch)
        }
      })
    },
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
    doneRearrange() {
      this.$refs.dataTable.fetch()
    }
  }
}
</script>