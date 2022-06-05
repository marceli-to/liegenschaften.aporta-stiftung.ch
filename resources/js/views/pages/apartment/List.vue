<template>
<div>
  <site-header :user="$store.state.user">
    <nav class="selector" v-if="hasFilter && isFetchedFilterItems">
      <div>
        <div class="grid-cols-12">
          <div class="span-1 start-2">
            <h2>Haus</h2>
            <div v-for="building in filterItems.buildings" :key="building.id">
              <a href="javascript:;" @click.prevent="setFilterItem('building_id', building.id)">
                <icon-radio :active="$store.state.filter.building_id == building.id" />
                <span>{{building.description}}</span>
              </a>
            </div>
          </div>
          <div class="span-2">
            <h2>Zimmer</h2>
            <div v-for="room in filterItems.rooms" :key="room.id">
              <a href="javascript:;" @click.prevent="setFilterItem('room_id', room.id)">
                <icon-radio :active="$store.state.filter.room_id == room.id" />
                <span>{{room.description}}</span>
              </a>
            </div>
          </div>
          <div class="span-2">
            <h2>Geschoss</h2>
            <div v-for="floor in filterItems.floors" :key="floor.id">
              <a href="javascript:;" @click.prevent="setFilterItem('floor_id', floor.id)">
                <icon-radio :active="$store.state.filter.floor_id == floor.id" />
                <span>{{floor.description}}</span>
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
              <a href="javascript:;" @click.prevent="setFilterItem('state_id', state.id)">
                <icon-radio :active="$store.state.filter.state_id == state.id" />
                <span>{{state.description}}</span>
              </a>
            </div>
          </div>
        </div>
      </div>
      <a href="javascript:;" :class="[$store.state.filter.set ? 'is-active' : '', 'btn-primary is-filter']" @click.prevent="hideFilter()">Anzeigen</a>
      <a href="javascript:;" class="btn-secondary is-outline" @click.prevent="resetFilter()">Zurücksetzen</a>
    </nav>
  </site-header>
  <site-main v-if="isFetched">
    <isometrie />
    <list v-if="sortedData">
      <list-header>
        <list-item :class="'span-1 list-item-header'">
          &nbsp;
        </list-item>
        <list-item :class="'span-2 list-item-header line-after'">
          Adresse
          <a href="" @click.prevent="sort('building.street')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Bezeichnung
          <a href="" @click.prevent="sort('description')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-2 list-item-header line-after'">
          Mieter*in
          <a href="" @click.prevent="sort('tenant.name')">
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
          <a href="" @click.prevent="sort('size')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Terrasse
          <a href="" @click.prevent="sort('size_terrace')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Sitzplatz
          <a href="" @click.prevent="sort('size_patio')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header'">
          Balkon
          <a href="" @click.prevent="sort('size_balcony')">
            <icon-sort />
          </a>
        </list-item>
        <!-- <list-item :class="'span-1 list-item-header'">
          Nummer
          <a href="" @click.prevent="sort('number')">
            <icon-sort />
          </a>
        </list-item> -->
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
        class="list-row" 
        :key="apartment.uuid" 
        @mouseover="show(apartment.number)" 
        @mouseleave="hide(apartment.number)">
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-action']">
          <a href="" @click.prevent="addToCollection(apartment.uuid)" v-if="!isInCollection(apartment.uuid)">
           <icon-plus :size="'md'" class="icon"  />
          </a>
          <a href="" @click.prevent="removeFromCollection(apartment.uuid)" v-if="isInCollection(apartment.uuid)">
           <icon-trash :size="'md'" class="icon" />
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
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.tenant ? apartment.tenant.full_name : '–' }}
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
        <!-- <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment.number }}
          </router-link>
        </list-item> -->
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
import ErrorHandling from "@/mixins/ErrorHandling";
import Helpers from "@/mixins/Helpers";
import Sort from "@/mixins/Sort";
import Filter from "@/views/pages/apartment/mixins/Filter";
import Collection from "@/views/pages/apartment/mixins/Collection";
import IconSort from "@/components/ui/icons/Sort.vue";
import IconState from "@/components/ui/icons/State.vue";
import IconRadio from "@/components/ui/icons/Radio.vue";
import IconPlus from "@/components/ui/icons/Plus.vue";
import IconTrash from "@/components/ui/icons/Trash.vue";
import Bullet from "@/components/ui/misc/Bullet.vue";
import SiteHeader from '@/views/layout/Header.vue';
import SiteMain from '@/views/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListRow from "@/components/ui/layout/ListRow.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import ListAction from "@/components/ui/layout/ListAction.vue";
import ListEmpty from "@/components/ui/layout/ListEmpty.vue";
import Isometrie from '@/views/pages/apartment/components/Isometrie.vue';

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
    IconTrash,
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
          states: '/api/settings/states'
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
      ]).then(axios.spread((...responses) => {
        this.filterItems = {
          buildings: responses[0].data,
          rooms: responses[1].data,
          floors: responses[2].data,
          exteriors: responses[3].data,
          states: responses[4].data,
        };
        this.isFetchedFilterItems = true;
      }));
    },

    fetchFiltered() {
      let param = {
        building_id: this.$store.state.filter.building_id ? this.$store.state.filter.building_id : null,
        room_id: this.$store.state.filter.room_id ? this.$store.state.filter.room_id : null,
        floor_id: this.$store.state.filter.floor_id ? this.$store.state.filter.floor_id : null,
        exterior: this.$store.state.filter.exterior ? this.$store.state.filter.exterior : null,
        state_id: this.$store.state.filter.state_id ? this.$store.state.filter.state_id : null,
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