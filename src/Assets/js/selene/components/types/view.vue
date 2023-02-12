<template>
  <div v-html="value"></div>
</template>

<script>
import seleneType from '../../mixins/input'

export default {
  mixins: [seleneType],
}
</script>