<template>
<div>
  <site-header></site-header>
  <site-main v-if="isMounted">
    <div class="grid-cols-12 collection">
      <div class="span-4 collection__intro">
        <p>Sehr geehrter Herr Burri</p>
        <p>Aufgrund Ihrer Präferenzen hat die Dr. Stephan à Porta-Stiftung die unten stehenden Wohnungen für Sie ausgesucht.</p>
      </div>
      <div class="span-5 collection__iso">
        <isometrie />
      </div>
      <div class="span-3 flex justify-end">
        <a :href="collection.estate.maps" target="_blank" title="Auf Google Maps anzeigen" class="link-maps">Google Maps</a>
      </div>  
    </div>

    <list v-if="sortedData">
      <list-header>
        <list-item :class="'span-2 list-item-header line-after'">
          Adresse
          <a href="" @click.prevent="sort('apartment.building.street')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-2 list-item-header line-after'">
          Bezeichnung
          <a href="" @click.prevent="sort('apartment.description')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-2 list-item-header line-after'">
          Mieter*in
          <a href="" @click.prevent="sort('apartmenttenant.name')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Zimmer
          <a href="" @click.prevent="sort('apartment.room.abbreviation')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          M<sup>2</sup>
          <a href="" @click.prevent="sort('apartment.size')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Terrasse
          <a href="" @click.prevent="sort('apartment.size_terrace')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Sitzplatz
          <a href="" @click.prevent="sort('apartment.size_patio')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Balkon
          <a href="" @click.prevent="sort('apartment.size_balcony')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header'">
          Bezug
          <a href="" @click.prevent="sort('size_balcony')">
            <icon-sort />
          </a>
        </list-item>
      </list-header>
      <div 
        v-for="(d, index) in sortedData" 
        class="list-row" 
        :key="d.uuid" 
        @mouseover="show(d.apartment.number)" 
        @mouseleave="hide(d.apartment.number)">
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <router-link :to="{name: 'collection-show', params: { uuid: collection.uuid, itemUuid: d.uuid }}">
            {{ d.apartment.building.street }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <router-link :to="{name: 'collection-show', params: { uuid: collection.uuid, itemUuid: d.uuid }}">
            {{ d.apartment.description }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <router-link :to="{name: 'collection-show', params: { uuid: collection.uuid, itemUuid: d.uuid }}">
            {{ d.apartment.tenant ? d.apartment.tenant.full_name : '–' }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'collection-show', params: { uuid: collection.uuid, itemUuid: d.uuid }}">
            {{ d.apartment.room.abbreviation }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'collection-show', params: { uuid: collection.uuid, itemUuid: d.uuid }}">
            {{ d.apartment.size }} m<sup>2</sup>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'collection-show', params: { uuid: collection.uuid, itemUuid: d.uuid }}">
            {{ d.apartment.size_terrace }} <span v-if="d.apartment.size_terrace > 0">m<sup>2</sup></span>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'collection-show', params: { uuid: collection.uuid, itemUuid: d.uuid }}">
            {{ d.apartment.size_patio }} <span v-if="d.apartment.size_patio > 0">m<sup>2</sup></span>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'collection-show', params: { uuid: collection.uuid, itemUuid: d.uuid }}">
            {{ d.apartment.size_balcony }} <span v-if="d.apartment.size_balcony > 0">m<sup>2</sup></span>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item']"></list-item>
      </div>
    </list>

  </site-main>
</div>
</template>
<script>
import NProgress from 'nprogress';
import ErrorHandling from '@/mixins/ErrorHandling';
import Sort from "@/mixins/Sort";
import SiteHeader from '@/views/frontend/layout/Header.vue';
import SiteMain from '@/views/frontend/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListRow from "@/components/ui/layout/ListRow.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import Isometrie from '@/components/ui/misc/Isometrie.vue';
import IconSort from "@/components/ui/icons/Sort.vue";

export default {

  components: {
    NProgress,
    SiteHeader,
    SiteMain,
    List,
    ListRow,
    ListHeader,
    ListItem,
    Isometrie,
    IconSort,
  },

  mixins: [ErrorHandling, Sort],

  data() {
    return {

      // Data
      data: [],

      // Collection
      collection: {},

      // States
      isFetched: false,
      isMounted: false,

      // Messages
      messages: {},
    };
  },


  mounted(){
    this.collection = this.$parent.$props.collection;
    this.data = this.$parent.$props.collection.items;
    this.isMounted = true;
  },

  methods: {

    show(number) {
      let apt = document.querySelector(`[data-id="${number}"]`);
      apt.classList.add('is-visible');
    },

    hide(number) {
      let apt = document.querySelector(`[data-id="${number}"]`);
      apt.classList.remove('is-visible');
    },
  }
}
</script>