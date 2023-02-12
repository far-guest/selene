<template>
  <div>
    <div id="selene-menu">
      <template v-for="(item, i) in menu">
        <component :item="item" :is="'selene-menu-' + item.c" @select="selected(i)" ref="items"></component>
      </template>
    </div>

    <form :action="route('logout')" method="post" class="logout" ref="logout">
      <input type="hidden" name="_token" :value="csrf">
      <a href="javascript:void(0)" @click="$refs.logout.submit()">
        <i class="fa fa-power-off fa-2x text-danger"></i>
      </a>
    </form>
  </div>
</template>

<script>

import SeleneMenuLink from "./selene-menu-link";
import SeleneMenuGroup from "./selene-menu-group";

export default {
  components: {SeleneMenuGroup, SeleneMenuLink},
  methods: {
    selected(i) {
      for (let x in this.$refs.items) {
        if (x != i && this.$refs.items[x].hasOwnProperty('hide'))
          this.$refs.items[x].hide()
      }
      this.$refs.items[i].show()
    }
  },
  computed: {
    menu() {
      return this.$store.state.menu
    },
    csrf() {
      return window.csrf
    },
  },
}
</script>

<style lang="scss" scoped>
.logout {
  margin: 20px 0;

  a {
    text-decoration: none;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 100%;
    width: 50px;
    height: 50px;
    margin: 0 auto;
    transition: 0.3s background-color;
    background: #ecf0f1;

    &:hover {
      background: #ffcece;
    }
  }
}
</style>