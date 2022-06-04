<template>
<div>
  <site-header :user="$store.state.user">

  </site-header>
  <site-main v-if="isFetched">
    <list v-if="sortedData.length">
      <list-row-header>
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
        <list-item :class="'span-1 list-item-header flex direction-column align-center'">
          <div>
            Status
            <a href="" @click.prevent="sort('state_id')">
              <icon-sort />
            </a>
          </div>
        </list-item>
      </list-row-header>
      <div 
        v-for="(apartment, index) in sortedData" 
        class="list-row" 
        :key="apartment.uuid" 
        @mouseover="show(apartment.number)" 
        @mouseleave="hide(apartment.number)">
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-action']">
          <a href="" @click.prevent="removeFromCollection(apartment.uuid, true)" v-if="isInCollection(apartment.uuid)">
           <icon-trash class="icon" />
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
    <form @submit.prevent="submit" class="collection" v-if="sortedData.length">
      <nav class="page-menu page-menu__collection">
        <ul>
          <li class="span-4 start-3">
            <a href="">
              <icon-cross />
              <span>Zurücksetzen</span>
            </a>
          </li>
          <li class="span-4">
            <a href="">
              <icon-cross />
              <span>Senden</span>
            </a>
          </li>
        </ul>
      </nav>
    </form>
  </site-main>
</div>
</template>
<script>
import NProgress from 'nprogress';
import ErrorHandling from "@/mixins/ErrorHandling";
import Helpers from "@/mixins/Helpers";
import Sort from "@/mixins/Sort";
import Filter from "@/views/pages/apartment/mixins/Filter";
import Selector from "@/views/pages/apartment/mixins/Selector";
import Collection from "@/views/pages/apartment/mixins/Collection";
import IconSort from "@/components/ui/icons/Sort.vue";
import IconState from "@/components/ui/icons/State.vue";
import IconRadio from "@/components/ui/icons/Radio.vue";
import IconRadioActive from "@/components/ui/icons/RadioActive.vue";
import IconPlus from "@/components/ui/icons/Plus-sm.vue";
import IconTrash from "@/components/ui/icons/Trash-sm.vue";
import IconCross from "@/components/ui/icons/Cross.vue";
import Bullet from "@/components/ui/misc/Bullet.vue";
import SiteHeader from '@/views/layout/Header.vue';
import SiteMain from '@/views/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListRowHeader from "@/components/ui/layout/ListRowHeader.vue";
import ListRow from "@/components/ui/layout/ListRow.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import ListAction from "@/components/ui/layout/ListAction.vue";
import ListEmpty from "@/components/ui/layout/ListEmpty.vue";

export default {

  components: {
    NProgress,
    SiteHeader,
    SiteMain,
    Bullet,
    IconSort,
    IconState,
    IconRadio,
    IconRadioActive,
    IconPlus,
    IconTrash,
    IconCross,
    List,
    ListRow,
    ListRowHeader,
    ListItem,
    ListAction,
    ListEmpty,
  },

  mixins: [ErrorHandling, Helpers, Sort, Filter, Selector, Collection],

  data() {
    return {

      // Data
      data: [],


      // Routes
      routes: {
        get: '/api/apartments',
      },

      // States
      isFetched: false,

      // Messages
      messages: {
        emptyData: 'Es sind noch keine Daten vorhanden...',
        updated: 'Status geändert',
      },
    };
  },

  mounted() {
    NProgress.configure({ showBar: false });
    this.fetch();
  },

  methods: {
    fetch() {
      NProgress.start();
      this.isFetched = false;
      this.axios.post(`${this.routes.get}`, this.$store.state.collection).then(response => {
        this.data = response.data.data;
        this.isFetched = true;
        NProgress.done();
      });
    },
    
  },

  watch: {

  },
}
</script>