<template>
    <div class="mb-3">
      <h5 class="pt-3 mb-3" v-html="panel.name" v-if="panel.name"></h5>
      <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" v-for="(field, index) in visibleFields">
                <a class="nav-link" :class="{'active':activeTab==index}" href="javascript:void(0)"
                   @click="changeTab(index)" v-text="field.title" :title="field.tooltip || field.title"></a>
            </li>
        </ul>
        <component :is="'selene-type-' + visibleFields[activeTab].type"
                   no-title
                   :resource="resource"
                   :values="values"
                   :display-type="2"
                   :primary-key="primaryKey"
                   :key="visibleFields[activeTab].name"
                   :field="visibleFields[activeTab]">
            <template slot="empty">
                <small class="text-muted">(وارد نشده)</small>
            </template>
        </component>
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
        methods: {
            changeTab(index) {
                let query = {};
                query['tab_' + this.index] = index || null;
                this.$router.replace({query: _.pickBy({...this.$route.query, ...query}, x => x != null)})
            }
        },
        computed: {
            activeTab() {
                return this.$route.query.hasOwnProperty(`tab_${this.index}`) ? this.$route.query[`tab_${this.index}`] : 0
            },
            visibleFields() {
                return _.filter(this.panel.fields, x => x.isVisibleOnDetails);
            },
        },
    }
</script>