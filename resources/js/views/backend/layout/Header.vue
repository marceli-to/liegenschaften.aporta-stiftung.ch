<template>
<div>
  <header :class="cls" @mouseleave="hideDropdown()">
    <div>
      <nav class="site-menu">
        <ul>
          <li class="span-2">
            <icon-logo class="icon-logo" />
          </li>
          <li class="span-2 page-title relative">
            <a href="javascript:;" class="dropdown-button" @mouseover="showDropdown()">
              {{ estateName }}
              <icon-chevron-down />
            </a>
            <div :class="[isDropdownOpen ? 'is-open' : '', 'dropdown']" @mouseleave="hideDropdown()">
              <router-link :to="{name: 'apartments'}">
                Objekte
              </router-link>
              <router-link :to="{name: 'tenants'}">
                Mieter
              </router-link>
              <a
                href=""
                class="is-estate"
                v-for="estate in otherEstates"
                :key="estate.key"
                @click.prevent="switchEstate(estate.key)">
                {{ estate.name }}
              </a>
            </div>
          </li>
          <li class="span-4 flex justify-center site-menu__pagination" v-if="view == 'show' || view == 'show-single'">
            <template v-if="store.filter.items.length && view == 'show'">
              <router-link :to="{name: 'apartment-show', params: { uuid: store.filter.menu.prev }}">
                <icon-arrow-left />
              </router-link>
              <span>{{ padStart(store.filter.menu.index) }} / {{ padStart(store.filter.items.length) }}</span>
              <router-link :to="{name: 'apartment-show', params: { uuid: store.filter.menu.next }}">
                <icon-arrow-right />
              </router-link>
            </template>
          </li>
          <li class="span-4 flex justify-center" v-else-if="view == 'tenants'">
            <a href="" @click.prevent="toggleSearch()">
              <icon-magnifier />
            </a>
          </li>
          <li class="span-4 flex justify-center" v-else>
            <router-link 
              :to="{name: 'apartments'}"
              class="icon-filter"
              v-if="$route.name == 'collection-create' || $route.name == 'collections' || $route.name == 'collection-edit' || $route.name == 'users'">
              <icon-filter :active="store.filter.set" />
            </router-link>
            <a 
              href="" 
              :class="[store.ui.hasFilter ? 'is-active' : '', 'icon-filter']" 
              @click.prevent="toggleFilter()"
              v-else>
              <icon-filter v-if="!store.ui.hasFilter" :active="store.filter.set" />
              <icon-cross v-if="store.ui.hasFilter" />
            </a>
          </li>
          <li class="span-1 flex justify-center">
            <router-link :to="{name: 'collection-create'}" class="icon-collection" v-if="store.collection.items.length > 0">
              <icon-collection :active="$route.name == 'collection' || store.collection.items.length > 0 ? true : false" />
              <span class="ml-2x">{{store.collection.items.length}}</span>
            </router-link>
            <div class="icon-collection is-disabled" v-else>
              <icon-collection />
            </div>
          </li>
          <li class="span-2 flex justify-center">
            <router-link 
              :to="{name: store.user.admin ? 'users' : 'user-profile'}"
              class="icon">
              <icon-user />
            </router-link>
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
<script setup>
import { ref, computed, inject } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import http from '@/lib/http';
import { store } from '@/store';
import { padStart } from '@/lib/utils';
import IconLogo from "@/components/ui/icons/Logo.vue";
import IconList from "@/components/ui/icons/List.vue";
import IconCollection from "@/components/ui/icons/Collection.vue";
import IconUser from "@/components/ui/icons/User.vue";
import IconFilter from "@/components/ui/icons/Filter.vue";
import IconCross from "@/components/ui/icons/Cross.vue";
import IconArrowLeft from "@/components/ui/icons/ArrowLeft.vue";
import IconArrowRight from "@/components/ui/icons/ArrowRight.vue";
import IconChevronDown from "@/components/ui/icons/ChevronDown.vue";
import IconMagnifier from "@/components/ui/icons/Magnifier.vue";

const props = defineProps({
  view: {
    type: String,
    default: ''
  },
});

const route = useRoute();
const router = useRouter();
const isDropdownOpen = ref(false);

const estateKey = inject('estateKey');
const estateName = inject('estateName');
const otherEstates = inject('estates').filter(estate => estate.key != estateKey);

// Reload on the apartment list: the page, the filter and the picked
// apartments all belong to the estate
function switchEstate(key) {
  hideDropdown();
  http.put('/api/estate', { key }).then(() => {
    window.location.href = router.resolve({ name: 'apartments' }).href;
  });
}

function toggleFilter() {
  store.ui.hasFilter = !store.ui.hasFilter;
}

function toggleSearch() {
  store.ui.hasSearch = !store.ui.hasSearch;
}

function showDropdown() {
  isDropdownOpen.value = true;
}

function hideDropdown() {
  isDropdownOpen.value = false;
}

const cls = computed(() => {
  let cls = 'site-header';
  if (store.ui.hasFilter) {
    cls = cls + ' has-filter';
  }
  if (store.ui.hasCollection) {
    cls = cls + ' has-collection';
  }
  if (store.ui.hasSearch) {
    cls = cls + ' has-search';
  }
  if (props.view == 'users') {
    cls = cls + ' is-users';
  }
  if (props.view == 'show' || props.view == 'show-single') {
    cls = cls + ' is-detail';
  }
  if (route.name == 'collections') {
    cls = cls + ' is-collection';
  }
  return cls;
});
</script>
