<template>
<div>
  <site-header></site-header>
  <site-main v-if="isFetched">
    <div v-if="isValid">
      <div class="sm:grid-cols-12">
        <h1 class="sm:hide">{{ $parent.$props.estate }}</h1>
        <div class="span-4 collection__intro mb-8x sm:mb-0">
          <p>Hier finden Sie sämtliche Informationen zu unserem Wohnungsangebot. Unter An-/Abmeldung haben Sie die Möglichkeit Ihre Rückmeldung direkt an uns zu richten. <strong>Achtung das Angebot ist nur 5 Tage gültig</strong>, wir bitten Sie um schnelle Rückmeldung.</p>
        </div>
        <div class="xs:hide span-5 collection__iso">
          <isometrie />
        </div>
        <div class="xs:hide span-3 flex justify-end">
          <a :href="estate.maps" target="_blank" title="Auf Google Maps anzeigen" class="link-maps">Google Maps</a>
        </div>  
      </div>
      <list v-if="sortedData" class="xs:hide">
        <list-header>
          <list-item :class="'span-2 list-item-header line-after'">
            Adresse
            <a href="" @click.prevent="sort('street')">
              <icon-sort />
            </a>
          </list-item>
          <list-item :class="'span-2 list-item-header line-after'">
            Lage
            <a href="" @click.prevent="sort('description')">
              <icon-sort />
            </a>
          </list-item>
          <list-item :class="'span-2 list-item-header line-after'">
            Mietzins Brutto
            <a href="" @click.prevent="sort('rent_gross')">
              <icon-sort />
            </a>
          </list-item>
          <list-item :class="'span-1 list-item-header line-after'">
            Zimmer
            <a href="" @click.prevent="sort('room')">
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
          <list-item :class="'span-1 list-item-header line-after'">
            Balkon
            <a href="" @click.prevent="sort('size_balcony')">
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
          @mouseover="show(d.number)" 
          @mouseleave="hide(d.number)">
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d.street }}
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d.description }}
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d.rent_gross }}
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d.rooms }}
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d.size }} m<sup>2</sup>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d.size_terrace }} <span v-if="d.size_terrace > 0">m<sup>2</sup></span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d.size_patio }} <span v-if="d.size_patio > 0">m<sup>2</sup></span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d.size_balcony }} <span v-if="d.size_balcony > 0">m<sup>2</sup></span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item']"></list-item>
        </div>
      </list>
      <list class="sm:hide list-xs" v-if="data">
        <list-item 
          v-for="d in sortedData" 
          :key="d.uuid" 
          class="list-item list-item__xs">
          <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
            <strong>{{ d.room_description }}, {{ d.size }} m<sup>2</sup></strong><br>
            {{ d.street }}, {{ d.city }}<br>
            {{ d.description }}
          </router-link>
        </list-item>
      </list>
    </div>
    <div v-else class="flex justify-center mt-15x">
      <p class="text-md"><strong>Dieses Angebot ist leider nicht mehr verfügbar.</strong></p>     
    </div>
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

      // Uuid
      uuid: null,

      // Items
      data: [],

      // Estate
      estate: {},

      // States
      isFetched: false,
      isValid: false,

      // Routes
      routes: {
        list: '/api/user-collection'
      },

      // Messages
      messages: {},
    };
  },


  mounted(){
    NProgress.configure({ showBar: false });
    this.fetch();
  },

  methods: {
    
    fetch() {
      NProgress.start();
      this.isFetched = false;
      this.axios.get(`${this.routes.list}/${this.$route.params.uuid}`).then(response => {
        this.data = response.data.items;
        this.uuid = response.data.uuid;
        this.estate = response.data.estate;
        this.isValid = response.data.valid;
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
  }
}
</script>