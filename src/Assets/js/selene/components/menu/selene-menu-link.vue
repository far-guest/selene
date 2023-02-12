<template>
  <div>
    <template v-if="item.type=='resource'">
      <div class="nav-item">
        <template v-if="item.page == 'index'">
          <router-link class="nav-link" :to="{name: 'resources', params: {resource: item.resource}}">
            <i class="fa fa-chevron-left"></i>
            <span v-text="item.title"></span>
          </router-link>
        </template>
        <template v-else-if="item.page == 'create'">
          <router-link class="nav-link" :to="{name: 'resources.create', params: {resource: item.resource}}">
            <i class="fa fa-chevron-left"></i>
            <span v-text="item.title"></span>
          </router-link>
        </template>
        <template v-else-if="item.page == 'edit'">
          <router-link class="nav-link"
                       :to="{name: 'resources.edit', params: {resource: item.resource, model:item.model}}">
            <i class="fa fa-chevron-left"></i>
            <span v-text="item.title"></span>
          </router-link>
        </template>
        <template v-else-if="item.page == 'details'">
          <router-link class="nav-link"
                       :to="{name: 'resources.details', params: {resource: item.resource, model:item.model}}">
            <i class="fa fa-chevron-left"></i>
            <span v-text="item.title"></span>
          </router-link>
        </template>
      </div>
    </template>

    <template v-if="item.type=='view'">
      <div class="nav-item">
        <router-link class="nav-link" :to="{name: 'views', params: {view: item.view}}">
          <i class="fa fa-chevron-left"></i>
          <span v-text="item.title"></span>
        </router-link>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: "selene-menu-link",
  props: {
    item: {
      required: true,
      type: Object
    }
  },
}
</script>
