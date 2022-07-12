<template>
<div>
  <site-header :user="$store.state.user">
    <nav class="selector" v-if="hasFilter && isFetchedFilterItems">
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
          <div class="span-2">
            <h2>Mietzins</h2>
            <div v-for="(value, key) in filterItems.rent" :key="key">
              <a href="javascript:;" @click.prevent="setFilterItem('rent', key)">
                <icon-radio :active="$store.state.filter['rent'] == key" />
                <span>{{value}}</span>
              </a>
            </div>
          </div>
          <div class="span-2">
            <h2>Aussenraum</h2>
            <div v-for="(value, key) in filterItems.exteriors" :key="key">
              <a href="javascript:;" @click.prevent="setFilterItem('exterior', key)">
                <icon-radio :active="$store.state.filter['exterior'] == key" />
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
                <icon-radio :active="$store.state.filter.collections == 1" />
                <span>Angebote</span>
              </a>
            </div>
          </div>
        </div>
      </div>
      <a href="javascript:;" :class="[$store.state.filter.set ? 'is-active' : '', 'btn-primary is-filter']" @click.prevent="hideFilter()">Anzeigen ({{ sortedData.length }})</a>
      <a href="javascript:;" class="btn-secondary is-outline" @click.prevent="resetFilter()">Zurücksetzen</a>
    </nav>
  </site-header>
  <site-main v-if="isFetched">
    <isometrie />
    <div class="my-6x pr-6x w-full align-right">
      <a href="/export/" target="_blank" class="link">Liste als Excel exportieren</a>
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
        <list-item :class="'span-1 list-item-header line-after'">
          Terrasse
          <a href="" @click.prevent="sort('sortable_size_terrace')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Sitzplatz
          <a href="" @click.prevent="sort('sortable_size_patio')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header'">
          Balkon
          <a href="" @click.prevent="sort('sortable_size_balcony')">
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
        @mouseover="show(apartment.number)" 
        @mouseleave="hide(apartment.number)">
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-action']">
          <a href="" @click.prevent="addToCollection(apartment.uuid)" v-if="!isInCollection(apartment.uuid)">
            <icon-checkbox class="icon icon-light" />
          </a>
          <a href="" @click.prevent="removeFromCollection(apartment.uuid)" v-if="isInCollection(apartment.uuid)">
           <icon-checkbox :active="'true'" class="icon" />
          </a> 
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.building.street }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.description }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.number }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            <!-- {{ apartment.tenant ? apartment.tenant.full_name : '–' }} -->
            {{ apartment.rent_gross }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.room.abbreviation }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.size }} m<sup>2</sup>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.size_terrace }} <span v-if="apartment.size_terrace > 0">m<sup>2</sup></span>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.size_patio }} <span v-if="apartment.size_patio > 0">m<sup>2</sup></span>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.size_balcony }} <span v-if="apartment.size_balcony > 0">m<sup>2</sup></span>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-state']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}" class="icon-state">
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
<script>
import NProgress from 'nprogress';
import ErrorHandling from '@/mixins/ErrorHandling';
import Helpers from "@/mixins/Helpers";
import Sort from "@/mixins/Sort";
import Filter from "@/views/backend/pages/mixins/Filter";
import Collection from "@/views/backend/pages/mixins/Collection";
import IconSort from "@/components/ui/icons/Sort.vue";
import IconState from "@/components/ui/icons/State.vue";
import IconRadio from "@/components/ui/icons/Radio.vue";
import IconPlus from "@/components/ui/icons/Plus.vue";
import IconCheckbox from "@/components/ui/icons/Checkbox.vue";
import Bullet from "@/components/ui/misc/Bullet.vue";
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListRow from "@/components/ui/layout/ListRow.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import ListAction from "@/components/ui/layout/ListAction.vue";
import ListEmpty from "@/components/ui/layout/ListEmpty.vue";
import Isometrie from '@/components/ui/misc/Isometrie.vue';

export default {

  components: {
    NProgress,
    SiteHeader,
    SiteMain,
    Bullet,
    IconSort,
    IconState,
    IconRadio,
    IconPlus,
    IconCheckbox,
    List,
    ListRow,
    ListHeader,
    ListItem,
    ListAction,
    ListEmpty,
    Isometrie
  },

  mixins: [ErrorHandling, Helpers, Sort, Filter, Collection],

  data() {
    return {

      // Data
      data: [],

      // Filter items
      filterItems: {
        buildings: [],
        rooms: [],
        floors: [],
        exteriors: [],
        states: [],
      },

      // Routes
      routes: {
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
      },

      // States
      isFetched: false,
      isFetchedFilterItems: false,

      // Messages
      messages: {
        emptyData: 'Es sind noch keine Wohnungen vorhanden...',
        updated: 'Status geändert',
      },
    };
  },

  mounted() {
    NProgress.configure({ showBar: false });
    this.beforeFetch()
  },

  methods: {

    beforeFetch() {
      this.fetchFilterItems();
      if (this.$store.state.filter.set) {
        this.fetchFiltered();
        return;
      }
      this.fetch();
    },

    fetch() {
      this.isFetched = false;
      NProgress.start();
      this.axios.get(`${this.routes.list}`).then(response => {
        this.data = response.data.data;
        this.isFetched = true;
        NProgress.done();
      });
    },

    fetchFilterItems() {
      this.isFetchedFilterItems = false;
      this.axios.all([
        this.axios.get(this.routes.settings.buildings),
        this.axios.get(this.routes.settings.rooms),
        this.axios.get(this.routes.settings.floors),
        this.axios.get(this.routes.settings.exteriors),
        this.axios.get(this.routes.settings.states),
        this.axios.get(this.routes.settings.rent),
      ]).then(axios.spread((...responses) => {
        this.filterItems = {
          buildings: responses[0].data,
          rooms: responses[1].data,
          floors: responses[2].data,
          exteriors: responses[3].data,
          states: responses[4].data,
          rent: responses[5].data,
        };
        this.isFetchedFilterItems = true;
      }));
    },

    fetchFiltered() {
      let param = {
        buildings: this.$store.state.filter.buildings ? this.$store.state.filter.buildings : null,
        rooms: this.$store.state.filter.rooms ? this.$store.state.filter.rooms : null,
        floors: this.$store.state.filter.floors ? this.$store.state.filter.floors : null,
        states: this.$store.state.filter.states ? this.$store.state.filter.states : null,
        rent: this.$store.state.filter.rent ? this.$store.state.filter.rent : null,
        collections: this.$store.state.filter.collections ? this.$store.state.filter.collections : null,
      };
      NProgress.start();
      this.isFetched = false;
      this.axios.post(`${this.routes.filter}`, param).then(response => {
        this.data = response.data.data;
        this.setFilterMenu(this.data);
        this.isFetched = true;
        NProgress.done();
      });
    },

    show(number) {
      let apt = document.querySelector(`[data-id="${number}"]`);
      apt.classList.add('is-visible');
    },

    hide(number) {
      let apt = document.querySelector(`[data-id="${number}"]`);
      apt.classList.remove('is-visible');
    },
  },

  watch: {
    '$route'() {
      this.beforeFetch()
    }
  },
}
</script>