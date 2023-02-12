<template>
  <div class="row d-flex align-items-stretch" v-if="metrics.length > 0">
    <div class="col-12 col-md-4 col-lg-3 mb-3 px-0"
         v-for="(metric, index) in metrics">
      <div class="bg-white border rounded p-2 mx-2">
        <selene-metric :metric="metric" :index="index" :url-generator="urlGenerator"></selene-metric>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    metrics: {
      type: Array,
      required: true,
    },
    urlGenerator: {
      type: Function,
      required: true,
    }
  }
}
</script>