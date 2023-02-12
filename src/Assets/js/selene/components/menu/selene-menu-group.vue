<template>
  <div>
    <template v-if="item.type=='flat'">
      <div class="p-2 mx-2 mb-1 sidebar-heading border rounded">
        <h6 class="p-0 m-0 d-flex justify-content-start align-items-center">
          <i class="mr-2" :class="item.icon"></i>
          <span v-text="item.title"></span>
        </h6>
      </div>

      <div class="nav flex-column mb-2">
        <template v-for="child in item.links">
          <selene-menu-link :item="child"/>
        </template>
      </div>
    </template>

    <template v-if="item.type=='drawer'">
      <div class="drawer">
        <a href="javascript:void(0)" class="nav-item drawer-link" :class="{active:active}" @click="$emit('select')">
          <span class="d-flex align-items-center justify-content-between">
            <span>
              <i class="mr-2" :class="item.icon"></i>
              <span v-text="item.title"></span>
            </span>
            <i class="fa float-left" :class="{'fa-caret-left':!active, 'fa-caret-down':active}"></i>
          </span>
        </a>

        <div class="collapse" ref="drawer">
          <template v-for="child in item.links">
            <selene-menu-link :item="child"/>
          </template>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import SeleneMenuLink from "./selene-menu-link";

export default {
  name: "selene-menu-group",
  components: {SeleneMenuLink},
  props: {
    item: {
      required: true,
      type: Object
    }
  },
  data() {
    return {active: false}
  },
  methods: {
    hide() {
      this.active = false
      $(this.$refs.drawer).collapse('hide')
    },
    show() {
      if (this.active) {
        this.hide()
      } else {
        this.active = true
        $(this.$refs.drawer).collapse('show')
      }
    }
  }
}
</script>