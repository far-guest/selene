<template>
  <div>
    <div v-if="loading">
      <div class="text-center py-3">
        <i class="fa fa-refresh fa-spin fa-3x"></i>
      </div>
    </div>
    <div v-html="content" v-else></div>
  </div>
</template>

<script>
import {mapActions} from 'vuex';

export default {
  props: {
    view: {
      required: true,
      type: String,
    }
  },
  data() {
    return {
      loading: false,
      content: null,
    }
  },
  methods: {
    ...mapActions([
      'si',
    ]),
  },
  mounted() {
    this.loading = true
    this.si({
      url: this.route('selene.views.render', this.view)
    }).then(x => {
      this.loading = false
      this.content = x
    }).catch(x => {
      this.loading = false
      this.content = '<div class="text-center py-3 text-danger"><i class="fa fa-exclamation-triangle fa-3x"></i></div>'
    })
  }
}
</script>