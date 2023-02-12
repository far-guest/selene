<script>
import {Line} from 'vue-chartjs'

export default {
  extends: Line,
  props: {
    x: {
      type: Array,
      required: true
    },
    ys: {
      type: Array,
      required: true,
    },
    opts: {
      type: Object,
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      chartData: {
        labels: this.x,
        datasets: this.ys
      },
    }
  },
  watch: {
    x() {
      this.$data._chart.update()
    },
    ys() {
      this.$data._chart.update()
    }
  },
  mounted() {
    this.renderChart(this.chartData, Object.assign({}, {
      responsive: true,
      maintainAspectRatio: false
    }, this.opts))
  }
}
</script>