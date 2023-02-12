<template>
  <div>
    <div class="d-flex justify-content-between align-items-end mb-3">
      <div class="d-flex justify-content-start align-items-center">
        <div v-if="backEnable">
          <button @click="$router.go(-1)" class="btn btn-link">
            <i class="fa fa-arrow-right"></i>
          </button>
          <span class="text-muted mr-3">/</span>
        </div>
        <h4 class="mb-0">
          <span v-text="'مشخصات ' + singular"></span>
          <a :href="res.details_help_link" target="_blank" class="text-muted" v-if="res.details_help_link">
            <small><i class="fa fa-question-circle"></i></small>
          </a>
        </h4>
      </div>

      <div>
        <div class="dropdown d-inline-block" v-if="Object.keys(actions).length > 0">
          <button class="btn btn-light bg-white border btn-group-append" type="button" data-toggle="dropdown">
            <i class="fa fa-play"></i>
            <span v-text="lang('action.custom_actions')"></span>
          </button>
          <div class="dropdown-menu rdropdown-menu-right">
            <template v-for="(action, id) in actions">
              <selene-action :resource="resource" :idx="id" :action="action" :models="[values[primaryKey]]">
                <template v-slot="{fire}">
                  <button class="dropdown-item" @click="fire">
                    <span v-text="action.title"></span>
                  </button>
                </template>
              </selene-action>
            </template>
          </div>
        </div>
        <button type="button" class="btn btn-light bg-white border" v-if="editEnable"
                @click="$router.push({name:'resources.edit', params:{resource:resource, model:values[primaryKey]}})">
          <i class="fa fa-edit"></i>
          <span v-text="lang('action.edit')"></span>
        </button>
        <button type="button" class="btn btn-light bg-white border text-danger" @click="remove" v-if="removeEnable">
          <i class="fa fa-trash"></i>
          <span v-text="lang('action.delete')"></span>
        </button>
      </div>
    </div>
    <div v-if="fetchComplete">
      <div v-for="(panel, index) in visiblePanels">
        <component :is="`selene-panel-${panel.type}`" :resource="resource" :panel="panel" :index="index"
                   :values="values"></component>
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
import resourceMixin from '../mixins/resource'

export default {
  mixins: [resourceMixin],
  props: {
    model: {
      type: [String, Number],
      required: true
    },
  },
  data() {
    return {
      values: {},
      loading: false,
      fetchComplete: false,
    }
  },
  computed: {
    isLoading() {
      return this.$store.state.loading
    },
    backEnable() {
      return true
    },
    visiblePanels() {
      return _.filter(this.panels, this.isVisible, [])
    }
  },
  methods: {
    ...mapActions([
      'error',
      'si',
    ]),
    isVisible(panel) {
      return _.filter(panel.fields, x => x.isVisibleOnDetails).length > 0;
    },
    remove(item) {
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
            url: this.route('selene.resources.destroy', [this.resource, this.values[this.primaryKey]]),
            method: 'post'
          }).then(() => this.$router.go(-1))
        }
      })
    }
  },
  created() {
    this.loading = true;
    this.si({
      url: this.route('selene.resources.details', [this.resource, this.model]),
      method: 'get'
    }).then(result => {
      this.values = result;
      this.fetchComplete = true;
      this.loading = false;
    }).catch(result => {
      this.loading = false;
    })
  }
}
</script>
