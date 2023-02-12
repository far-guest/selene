<template>
  <div>
    <b-modal ref="form" title="چیدمان" v-if="true" hide-footer @hidden="rows=[]">
      <ajax-form ref="ajaxForm" :custom-submit="true" @submit="submitAndReset">
        <div class="text-center" v-if="isLoading">
          <i class="fa fa-refresh fa-3x fa-spin"></i>
        </div>
        <div class="input-fields" v-else>

          <div v-if="rows.length > 0">
            <draggable v-model="rows" class="list-group pl-0">
              <div class="list-group-item" v-for="element in rows" :key="element.key">{{ element.title }}</div>
            </draggable>

            <div class="mt-3 pt-3 px-3 border-top text-right" style="margin-left: -1rem; margin-right: -1rem">
              <button class="btn btn-primary" @click="$refs.ajaxForm.submit()" :disabled="isLoading">
                <i class="fa fa-refresh fa-spin" v-if="isLoading"></i>
                <span v-else>تایید</span>
              </button>
            </div>
          </div>
          <div v-else>
            <div class="alert alert-warning mb-0">
              <i class="fa fa-exclamation-circle"></i>
              <span>داده ای وجود ندارد</span>
            </div>
          </div>
        </div>
      </ajax-form>
    </b-modal>
  </div>
</template>
<script>
import {mapActions} from "vuex";
import draggable from 'vuedraggable'

export default {
  components: {
    draggable,
  },
  props: {
    retrieveUrl: {
      required: true,
    },
  },
  data() {
    return {
      rows: []
    }
  },
  methods: {
    ...mapActions([
      'si',
    ]),
    submitAndReset() {
      this.si({url: this.retrieveUrl, method: 'post', data: {keys: this.rows.map(x => x.key)}}).then(() => {
        this.$refs.form.hide()
        this.$emit('done')
      })
    },
    show() {
      this.$refs.form.show()
      this.si({url: this.retrieveUrl}).then(data => {
        this.rows = data
      })
    }
  },
  computed: {
    isLoading() {
      return this.$store.state.loading
    },
  },
}
</script>