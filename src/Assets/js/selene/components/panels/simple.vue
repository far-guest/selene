<template>
  <div>
    <h5 class="pt-3 mb-3" v-html="panel.name" v-if="!panel.support && panel.name"></h5>
    <div class="mb-3" :class="{'card': panel.support}">
      <div class="card-header bg-white" v-if="panel.support">
        <div class="d-flex justify-content-between">
          <span v-text="panel.name"></span>
          <a data-toggle="collapse" :href="'#panel-body-' + index" aria-expanded="true"
             :aria-controls="'panel-body-' + index">
            <i class="fa" :class="{'fa-window-minimize': display, 'fa-chevron-down': !display}"></i>
          </a>
        </div>
      </div>
      <div :class="{'card-body py-0 collapse': panel.support, 'show': panel.support && display}" :id="'panel-body-' + index" ref="col">
        <div :class="{'border-top': panel.support && index > 0, 'py-4': panel.support}"
             v-for="(field, index) in visibleFields(panel.fields)">
          <component :is="'selene-type-' + field.type" :resource="resource" :field="field"
                     :display-type="2" :primary-key="primaryKey" :values="values">
            <template slot="empty">
              <small class="text-muted">(وارد نشده)</small>
            </template>
          </component>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import resourceMixin from '../../mixins/resource'

export default {
  mixins: [resourceMixin],
  props: {
    resource: {
      required: true
    },
    panel: {
      required: true
    },
    index: {
      required: true
    },
    values: {
      required: true
    }
  },
  data() {
    return {
      display: true
    }
  },
  methods: {
    visibleFields(fields) {
      return _.filter(fields, x => x.isVisibleOnDetails);
    },
  },
  mounted() {
    if (!this.panel.visible)
      this.display = false

    $(this.$refs['col']).on('hidden.bs.collapse', () => {
      this.display = false
    }).on('shown.bs.collapse', () => {
      this.display = true
    })
  }
}
</script>