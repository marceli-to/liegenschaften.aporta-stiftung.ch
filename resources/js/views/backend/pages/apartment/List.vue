<template>
<div>
  <site-header>
    <nav class="selector" v-if="store.ui.hasFilter && isFetchedFilterItems">
      <div>
        <div class="grid-cols-12">
          <div class="span-2">
            <h2>Haus</h2>
            <div v-for="building in filterItems.buildings" :key="building.id">
              <a href="javascript:;" @click.prevent="setFilterItem('buildings', building.id)">
                <icon-radio :active="isFilterAttribute('buildings', building.id)" />
                <span>{{building.street}}</span>
              </a>
            </div>
          </div>
          <div class="span-2">
            <h2>Zimmer</h2>
            <div v-for="room in filterItems.rooms" :key="room.id">
              <a href="javascript:;" @click.prevent="setFilterItem('rooms', room.id)">
                <icon-radio :active="isFilterAttribute('rooms', room.id)" />
                <span>{{room.abbreviation}}</span>
              </a>
            </div>
          </div>
          <div class="span-2">
            <h2>Geschoss</h2>
            <div v-for="floor in filterItems.floors" :key="floor.id">
              <a href="javascript:;" @click.prevent="setFilterItem('floors', floor.id)">
                <icon-radio :active="isFilterAttribute('floors', floor.id)" />
                <span>{{floor.abbreviation}}</span>
              </a>
            </div>
          </div>
          <!--
          <div class="span-2">
            <h2>Mietzins</h2>
            <div v-for="(value, key) in filterItems.rent" :key="key">
              <a href="javascript:;" @click.prevent="setFilterItem('rent', key)">
                <icon-radio :active="store.filter['rent'] == key" />
                <span>{{value}}</span>
              </a>
            </div>
          </div>
          -->
          <div class="span-2">
            <h2>Aussenraum</h2>
            <div v-for="(value, key) in filterItems.exteriors" :key="key">
              <a href="javascript:;" @click.prevent="setFilterItem('exterior', key)">
                <icon-radio :active="store.filter['exterior'] == key" />
                <span>{{value}}</span>
              </a>
            </div>
          </div>
          <div class="span-2">
            <h2>Status</h2>
            <div v-for="state in filterItems.states" :key="state.id">
              <a href="javascript:;" @click.prevent="setFilterItem('states', state.id)">
                <icon-radio :active="isFilterAttribute('states', state.id)" />
                <span>{{state.description}}</span>
              </a>
            </div>
            <div>
              <a href="javascript:;" @click.prevent="setFilterItem('collections', 1)">
                <icon-radio :active="store.filter.collections == 1" />
                <span>Angebote</span>
              </a>
            </div>
          </div>
        </div>
      </div>
      <a href="javascript:;" :class="[store.filter.set ? 'is-active' : '', 'btn-primary is-filter']" @click.prevent="hideFilter()">Anzeigen ({{ sortedData.length }})</a>
      <a href="javascript:;" class="btn-secondary is-outline" @click.prevent="resetFilter()">Zurücksetzen</a>
    </nav>
  </site-header>
  <site-main v-if="isFetched">
    <isometrie :estate="estateKey" :active="hovered" />
    <div class="my-6x pr-6x w-full align-right">
      <a :href="`/export/objekte?v=${randomString()}`" target="_blank" class="link-export">
        Export Excel
        <icon-document class="ml-1x" />
      </a>
    </div>
    <list v-if="sortedData">
      <list-header>
        <list-item :class="'span-1 list-item-header flex justify-center'">
          Merken
        </list-item>
        <list-item :class="'span-2 list-item-header line-after'">
          Adresse
          <a href="" @click.prevent="sort('building.street')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Lage
          <a href="" @click.prevent="sort('description')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Nummer
          <a href="" @click.prevent="sort('number')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Mietzins
          <a href="" @click.prevent="sort('sortable_rent')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Zimmer
          <a href="" @click.prevent="sort('room.abbreviation')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          M<sup>2</sup>
          <a href="" @click.prevent="sort('sortable_size')">
            <icon-sort />
          </a>
        </list-item>
        <list-item
          v-for="(label, key, i) in exteriors"
          :key="key"
          :class="['span-1 list-item-header', i < Object.keys(exteriors).length - 1 ? 'line-after' : '']">
          {{ label }}
          <a href="" @click.prevent="sort(`sortable_size_${key}`)">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header flex direction-column align-center'">
          <div>
            Status
            <a href="" @click.prevent="sort('state_id')">
              <icon-sort />
            </a>
          </div>
        </list-item>
      </list-header>
      <div 
        v-for="(apartment, index) in sortedData" 
        :class="[apartment.collection_items.length > 0 ? 'has-collections' : '', 'list-row']" 
        :key="apartment.uuid" 
        @mouseover="hovered = apartment.number" 
        @mouseleave="hovered = null">
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-action']">
          <a href="" @click.prevent="addToCollection(apartment.uuid)" v-if="!isInCollection(apartment.uuid)">
            <icon-checkbox class="icon icon-light" />
          </a>
          <a href="" @click.prevent="removeFromCollection(apartment.uuid)" v-if="isInCollection(apartment.uuid)">
           <icon-checkbox :active="'true'" class="icon" />
          </a> 
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid, referrer: 'apartments' }}">
            {{ apartment.building.street }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid, referrer: 'apartments' }}">
            {{ apartment.description }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid, referrer: 'apartments' }}">
            {{ apartment.number }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid, referrer: 'apartments' }}">
            {{ apartment.rent_gross }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid, referrer: 'apartments' }}">
            {{ apartment.room.abbreviation }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid, referrer: 'apartments' }}">
            {{ apartment.size }} m<sup>2</sup>
          </router-link>
        </list-item>
        <list-item v-for="(label, key) in exteriors" :key="key" :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid, referrer: 'apartments' }}">
            {{ apartment['size_' + key] }} <span v-if="apartment['size_' + key] > 0">m<sup>2</sup></span>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-state']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid, referrer: 'apartments' }}" class="icon-state">
            <icon-state :id="apartment.state_id" />
          </router-link>
        </list-item>
      </div>
    </list>
    <list-empty v-else>
      {{messages.emptyData}}
    </list-empty>
  </site-main>
</div>
</template>
<script setup>
import { ref, inject, onMounted } from 'vue';
import NProgress from 'nprogress';
import http from '@/lib/http';
import { store } from '@/store';
import { randomString } from '@/lib/utils';
import { useSort } from '@/composables/useSort';
import { useFilter } from '@/composables/useFilter';
import { useCollection } from '@/composables/useCollection';
import IconSort from "@/components/ui/icons/Sort.vue";
import IconState from "@/components/ui/icons/State.vue";
import IconRadio from "@/components/ui/icons/Radio.vue";
import IconDocument from "@/components/ui/icons/Document.vue";
import IconCheckbox from "@/components/ui/icons/Checkbox.vue";
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import ListEmpty from "@/components/ui/layout/ListEmpty.vue";
import Isometrie from '@/components/ui/misc/Isometrie.vue';

const estateKey = inject('estateKey');

// Columns of the list (key => label)
const exteriors = inject('exteriors');

// Highlighted in the isometry
const hovered = ref(null);

const data = ref([]);

const filterItems = ref({
  buildings: [],
  rooms: [],
  floors: [],
  exteriors: [],
  states: [],
});

const routes = {
  list: '/api/apartments',
  filter: '/api/apartments/filter',
  settings: {
    buildings: '/api/settings/buildings',
    rooms: '/api/settings/rooms',
    floors: '/api/settings/floors',
    exteriors: '/api/settings/exteriors',
    states: '/api/settings/states',
    rent: '/api/settings/rent',
  }
};

const isFetched = ref(false);
const isFetchedFilterItems = ref(false);

const messages = {
  emptyData: 'Es sind noch keine Wohnungen vorhanden...',
};

const { sort, sortedData } = useSort(data);
const { addToCollection, removeFromCollection, isInCollection } = useCollection();
const { hideFilter, resetFilter, isFilterAttribute, setFilterItem, setFilterMenu } = useFilter({ fetch, fetchFiltered });

onMounted(() => {
  fetchFilterItems();
  if (store.filter.set) {
    fetchFiltered();
    return;
  }
  fetch();
});

function fetch() {
  isFetched.value = false;
  NProgress.start();
  http.get(routes.list).then(response => {
    data.value = response.data.data;
    isFetched.value = true;
    NProgress.done();
  });
}

function fetchFilterItems() {
  isFetchedFilterItems.value = false;
  const settings = routes.settings;
  Promise.all([
    http.get(settings.buildings),
    http.get(settings.rooms),
    http.get(settings.floors),
    http.get(settings.exteriors),
    http.get(settings.states),
    http.get(settings.rent),
  ]).then(responses => {
    filterItems.value = {
      buildings: responses[0].data,
      rooms: responses[1].data,
      floors: responses[2].data,
      exteriors: responses[3].data,
      states: responses[4].data,
      rent: responses[5].data,
    };
    isFetchedFilterItems.value = true;
  });
}

function fetchFiltered() {
  const filter = store.filter;
  const param = {
    buildings: filter.buildings ? filter.buildings : null,
    rooms: filter.rooms ? filter.rooms : null,
    floors: filter.floors ? filter.floors : null,
    states: filter.states ? filter.states : null,
    rent: filter.rent ? filter.rent : null,
    collections: filter.collections ? filter.collections : null,
    exterior: filter.exterior ? filter.exterior : null,
  };
  NProgress.start();
  isFetched.value = false;
  http.post(routes.filter, param).then(response => {
    data.value = response.data.data;
    setFilterMenu(data.value);
    isFetched.value = true;
    NProgress.done();
  });
}
</script>
