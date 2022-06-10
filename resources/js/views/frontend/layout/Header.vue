<template>
<div>
  <header :class="cls" v-if="isMounted">
    <div>
      <nav class="site-menu">
        <ul>
          <li class="span-2">
            <icon-logo class="icon-logo" />
          </li>
          <li class="span-6 page-title">
            <h1>{{ collection.estate.description_long }}, {{ collection.estate.city }}</h1>
          </li>
          <li class="span-4 page-title flex justify-end">
            <h2>Ihre Wohnungsangebote</h2>
          </li>
        </ul>
      </nav>
    </div>
  </header>
  <slot />
</div>
</template>
<script>
import IconLogo from "@/components/ui/icons/Logo.vue";

export default {
  components: {
    IconLogo,
  },

  props: {
    view: {
      type: String,
      default: 'list'
    },
  },

  data() {
    return {
      collection: {},
      isMounted: false,
    }
  },

  mounted() {
    this.collection = this.$parent.$parent.$props.collection;
    this.isMounted = true;
  },

  methods: {

  },

  computed: {
    cls() {
      let cls = 'site-header';
      if (this.$props.view == 'show') {
        cls = cls + ' is-collection-detail';
      }
      if (this.$props.view == 'list') {
        cls = cls + ' is-collection-list';
      }
      return cls; 
    }
  },
}
</script>
