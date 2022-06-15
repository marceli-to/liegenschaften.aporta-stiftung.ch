<template>
<div>
  <header :class="cls">
    <div>
      <nav class="site-menu">
        <ul>
          <li class="span-2">
            <icon-logo class="icon-logo" />
          </li>
          <li class="span-2 page-title">
            <router-link :to="{name: 'apartments'}">
              Eglistrasse
            </router-link>
          </li>
          <li class="span-4 flex justify-center site-menu__pagination" v-if="$props.view == 'show'">
            <template v-if="$store.state.filter.items.length">
              <router-link :to="{name: 'apartment-show', params: { uuid: $store.state.filter.menu.prev }}">
                <icon-arrow-left />
              </router-link>
                <span>{{$store.state.filter.menu.index | padStart}} / {{$store.state.filter.items.length | padStart}}</span>
              <router-link :to="{name: 'apartment-show', params: { uuid: $store.state.filter.menu.next }}">
                <icon-arrow-right />
              </router-link>
            </template>
          </li>
          <li class="span-4 flex justify-center" v-else>
            <router-link 
              :to="{name: 'apartments'}"
              class="icon-filter"
              v-if="$route.name == 'collection' || $route.name == 'collections'">
              <icon-filter :active="$store.state.filter.set" />
            </router-link>
            <a 
              href="" 
              :class="[$parent.hasFilter ? 'is-active' : '', 'icon-filter']" 
              @click.prevent="toggleFilter()"
              v-else>
              <icon-filter v-if="!$parent.hasFilter" :active="$store.state.filter.set" />
              <icon-cross v-if="$parent.hasFilter" />
            </a>
          </li>
          <li class="span-1 flex justify-center">
            <router-link :to="{name: 'collection'}" class="icon-collection" v-if="$store.state.collection.items.length > 0">
              <icon-collection :active="$route.name == 'collection' || $store.state.collection.items.length > 0 ? true : false" />
              <span class="ml-2x">{{$store.state.collection.items.length}}</span>
            </router-link>
            <div class="icon-collection is-disabled" v-else>
              <icon-collection />
            </div>
          </li>
          <li class="span-2 flex justify-center">
            <a href="/logout" class="icon-user">
              <icon-user />
            </a>
          </li>
          <li class="span-1 flex justify-center">
            <router-link 
              :to="{name: 'apartments'}"
              class="icon-offer"
              v-if="$route.name == 'collections'">
              <icon-cross />
            </router-link>
            <router-link 
              :to="{name: 'collections'}" 
              class="icon-offer"
              v-else>
              <icon-cross v-if="$route.name == 'collections'" />
              <icon-list v-else />
            </router-link>
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
import IconList from "@/components/ui/icons/List.vue";
import IconCollection from "@/components/ui/icons/Collection.vue";
import IconUser from "@/components/ui/icons/User.vue";
import IconFilter from "@/components/ui/icons/Filter.vue";
import IconCross from "@/components/ui/icons/Cross.vue";
import IconArrowLeft from "@/components/ui/icons/ArrowLeft.vue";
import IconArrowRight from "@/components/ui/icons/ArrowRight.vue";

export default {
  components: {
    IconCollection,
    IconFilter,
    IconCross,
    IconUser,
    IconArrowLeft,
    IconArrowRight,
    IconLogo,
    IconList
  },

  props: {
    user: '',
    view: {
      type: String,
      default: ''
    },
  },

  methods: {
    toggleFilter() {
      this.$parent.toggleFilter();
    },
    toggleSelector() {
      this.$parent.toggleSelector();
    },
  },

  watch: {
    '$route'() {
      this.hasFilter = false;
      this.hasExport = false;
    }
  },

  computed: {
    cls() {
      let cls = 'site-header';
      if (this.$parent.hasFilter) {
        cls = cls + ' has-filter';
      }
      if (this.$parent.hasCollection) {
        cls = cls + ' has-collection';
      }
      if (this.$props.view == 'show') {
        cls = cls + ' is-detail';
      }
      if (this.$route.name == 'collections') {
        cls = cls + ' is-collection';
      }
      return cls; 
    }
  },
}
</script>
