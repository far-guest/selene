<template>
  <div>
    <template v-if="index && index.type">
      <selene-resource-index :resource="index.resource" v-if="index.type=='resource'"></selene-resource-index>
    </template>
  </div>
</template>

<script>
export default {
  computed: {
    index() {
      return this.$store.state.index
    }
  }
}
</script>