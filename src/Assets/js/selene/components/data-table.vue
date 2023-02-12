<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="w-50">
        <div class="d-flex align-items-center bg-white border rounded" v-if="searchBar">
          <i class="fa fa-search mx-3" aria-hidden="true"></i>
          <b-form-input class="border-0" placeholder="جستجو" v-model="search"></b-form-input>
        </div>
      </div>

      <div>
        <button @click="$emit('rearrange')"
                class="btn btn-outline-primary px-3"
                :disabled="isLoading" v-if="rearrange">
          <span>چیدمان</span>
        </button>

        <button @click="$emit('create')"
                class="btn btn-primary px-3"
                :disabled="isLoading" v-if="createEnable">
          <span v-html="createText"></span>
        </button>
      </div>
    </div>

    <div v-if="models">
      <div class="mb-3">
        <div class="table-responsive bg-white">
          <table class="table table-striped border mb-0">
            <thead>
            <tr>
              <th v-if="indexColumn">
                <small class="text-muted">
                  <i class="fa fa-hashtag"></i>
                </small>
              </th>
              <th :class="{'bg-light': sort == field.name}" v-for="field in fields">
                <component :is="field.sortable ? 'a' : 'div'"
                           :href="field.sortable ? 'javascript:void(0)' : null"
                           :class="{'link-unstyled': field.sortable}"
                           v-on="field.sortable ? {click: () => $nextTick(() => sortBy(field.name))} : {}">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-start align-items-center">
                      <span v-text="field.title"></span>
                      <div class="ml-3" v-if="field.filterable">
                        <selene-field-filter-dialog :resource="resource"
                                                  :field="field"
                                                  :param-prefix="paramPrefix"
                                                  v-model="relations[field.name]"></selene-field-filter-dialog>
                      </div>
                    </div>

                    <div class="ml-2" v-if="field.sortable">
                      <small>
                        <i class="fa fa-sort text-muted" v-if="sort != field.name"></i>
                        <i class="fa fa-sort-amount-desc" v-else-if="sortDesc"></i>
                        <i class="fa fa-sort-amount-asc" v-else></i>
                      </small>
                    </div>
                  </div>
                </component>
              </th>
              <th width="1%"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(item, key) in models.data">
              <td v-if="indexColumn">
                <small class="text-muted"
                       v-text="(models.per_page * (models.current_page-1)) + (key+1)"></small>
              </td>
              <td v-for="field in fields">
                <component :is="'selene-type-' + field.type" :resource="resource" :field="field"
                           :values="item" :display-type="1">
                  <template slot="empty">
                    <small class="text-muted">(وارد نشده)</small>
                  </template>
                </component>
              </td>
              <td class="pt-2 pb-0">
                <div class="d-flex justify-content-end flex-nowrap text-lg">
                  <a href="javascript:void(0)" class="text-dark" @click="details(item)"
                     v-if="detailsEnable && (!item.hasOwnProperty('__perms') || item.__perms.view)">
                    <i class="fa fa-eye"></i>
                  </a>
                  <a href="javascript:void(0)" class="text-dark ml-3" @click="edit(item)"
                     v-if="editEnable && (!item.hasOwnProperty('__perms') || item.__perms.edit)">
                    <i class="fa fa-edit"></i>
                  </a>
                  <a href="javascript:void(0)" class="text-dark ml-3" @click="remove(item)"
                     v-if="removeEnable && (!item.hasOwnProperty('__perms') || item.__perms.delete)">
                    <i class="fa fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
          <div class="text-center border border-top-0 bg-light py-4" v-if="models.total < 1">
            <div class="mb-2">
              <i class="fa fa-table fa-3x"></i>
            </div>
            <div><strong v-html="`هیچ ${singular}ی یافت نشد!`"></strong></div>
          </div>
        </div>
      </div>
      <div v-if="models.total > 0 && models.total > models.per_page">
        <div>
          <div class="d-flex justify-content-between align-items-center">
            <b-pagination
                class="mb-0"
                :disabled="isLoading"
                :total-rows="models.total"
                :per-page="models.per_page"
                v-model="page"
                @change="pageChanged"></b-pagination>

            <div class="mx-3">
              <strong v-html="models.total"></strong>
              <span>نتیجه در</span>
              <strong v-html="Math.ceil(models.total / models.per_page)"></strong>
              <span>صفحه</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="loading">
      <div class="text-center py-3">
        <i class="fa fa-refresh fa-spin fa-3x"></i>
      </div>
    </div>
  </div>
</template>
<script>
import {mapActions} from 'vuex';

export default {
  props: {
    resource: {
      required: true,
      type: String
    },
    singular: {
      required: true,
      type: String
    },
    plural: {
      required: true,
      type: String
    },
    fields: {
      required: true,
      type: [Array, Object]
    },
    createEnable: {
      default: false,
      type: Boolean
    },
    createText: {
      default: 'ایجاد',
    },
    detailsEnable: {
      default: false,
      type: Boolean
    },
    editEnable: {
      default: false,
      type: Boolean
    },
    removeEnable: {
      default: false,
      type: Boolean
    },
    primaryKey: {
      default: 'id',
      type: String
    },
    paramPrefix: {
      default: '',
      type: String
    },
    fetchUrl: {
      default: function () {
        return route('selene.resources.paginate', this.resource)
      },
      type: String,
    },
    indexColumn: {
      type: Boolean,
      default: false,
    },
    searchBar: {
      type: Boolean,
      default: true
    },
    rearrange: {
      type: Boolean,
      default: false,
    }
  },
  data() {
    return {
      page: 1,
      models: null,
      timeout: null,
      search: null,
      sort: null,
      sortDesc: null,
      relations: {},
      loading: false,
    }
  },
  computed: {
    isLoading() {
      return this.$store.state.loading
    },
    filters() {
      let count = 0, filters = {};

      _.each(this.relations, (value, key) => {
        if (value != null && value.length > 0) {
          filters[key] = value;
          count++;
        }
      });

      return count > 0 ? filters : null
    }
  },
  watch: {
    search() {
      this.delay(() => {
        this.page = 1;
        this.updateQuery()
      })
    },
    sort(v1, v2) {
      if (v1 != v2)
        this.updateQuery()
    },
    sortDesc(v) {
      if (v != null)
        this.updateQuery()
    },
    relations: {
      handler() {
        this.page = 1;
        this.updateQuery()
      },
      deep: true
    },
  },
  methods: {
    ...mapActions([
      'si',
    ]),
    delay(x) {
      this.timeout == null || clearTimeout(this.timeout);
      this.timeout = setTimeout(x, 1000)
    },
    buildQuery() {
      let query = {};

      query[this.paramPrefix + 'page'] = this.page > 1 ? this.page : null;
      query[this.paramPrefix + 'q'] = this.search ? this.search : null;
      query[this.paramPrefix + 's'] = this.sort ? this.sort : null;
      query[this.paramPrefix + 'd'] = this.sortDesc ? 1 : null;

      if (this.relations) {
        let q = _.reduce(this.relations, (carry, item, key) => {
          if (item != null && item.length > 0) {
            carry[key] = item
            // _.map(item, x => x.id)
          }
          return carry
        }, {});

        query[this.paramPrefix + 'f'] = _.size(q) > 0 ? JSON.stringify(q) : null;
      } else {
        query[this.paramPrefix + 'f'] = null;
      }

      return query
    },
    pageChanged() {
      this.$nextTick(x => {
        this.updateQuery()
      })
    },
    sortBy(field) {
      if (this.sort == field) {
        this.sortDesc = !this.sortDesc
      } else {
        this.sort = field;
        this.sortDesc = null;
      }
    },
    updateQuery() {
      this.$router.replace({query: _.pickBy({...this.$route.query, ...this.buildQuery()}, x => x != null)})
    },
    fetch() {
      this.loading = true;
      this.si({
        url: this.fetchUrl,
        data: {
          page: this.page,
          search: this.search,
          filters: this.filters,
          sort: this.sort,
          desc: this.sortDesc ? 1 : null,
        },
        method: 'get'
      }).then(result => {
        this.page = result.current_page;
        this.models = result;
        this.loading = false;
      }).catch(result => {
        this.loading = false;
      });
    },
    details(item) {
      this.$emit('details', item[this.primaryKey])
    },
    edit(item) {
      this.$emit('edit', item[this.primaryKey])
    },
    remove(item) {
      this.$emit('remove', item[this.primaryKey])
    },
  },
  mounted() {
    this.fetch();
  },
  created() {
    if (this.$route.query.hasOwnProperty(this.paramPrefix + 'page')) {
      this.page = this.$route.query[this.paramPrefix + 'page']
    }

    if (this.$route.query.hasOwnProperty(this.paramPrefix + 'q')) {
      this.search = this.$route.query[this.paramPrefix + 'q']
    }

    if (this.$route.query.hasOwnProperty(this.paramPrefix + 'f')) {
      let q = JSON.parse(this.$route.query[this.paramPrefix + 'f']);

      _.each(q, (value, key) => {
        this.$set(this.relations, key, value)
      });
    }

    if (this.$route.query.hasOwnProperty(this.paramPrefix + 's')) {
      this.sort = this.$route.query[this.paramPrefix + 's']
    }

    if (this.$route.query.hasOwnProperty(this.paramPrefix + 'd')) {
      this.sortDesc = true
    }

    this.$watch(`$route.query.${this.paramPrefix}page`, this.fetch);
    this.$watch(`$route.query.${this.paramPrefix}q`, this.fetch);
    this.$watch(`$route.query.${this.paramPrefix}f`, this.fetch);
    this.$watch(`$route.query.${this.paramPrefix}s`, this.fetch);
    this.$watch(`$route.query.${this.paramPrefix}d`, this.fetch);
  }
}
</script>