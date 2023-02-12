<template>
  <div>
    <template v-if="field.chartType == 'bar'">
      <cbar :x="value.x" :ys="value.ys" :opts="field.chartOptions"/>
    </template>
    <template v-if="field.chartType == 'line'">
      <cline :x="value.x" :ys="value.ys" :opts="field.chartOptions"/>
    </template>
  </div>
</template>
<script>
import Chart from "chart.js";
import seleneType from '../../mixins/input'
import Cline from "../charts/cline";
import Cbar from "../charts/cbar";

Chart.defaults.global.defaultFontFamily = 'IRANSans, Helvetica, Arial, sans-serif'

export default {
  components: {Cbar, Cline},
  mixins: [seleneType],
}
</script>