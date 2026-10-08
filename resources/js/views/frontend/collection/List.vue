<template>
<div>
  <site-header></site-header>
  <site-main v-if="isFetched">
    <div v-if="isValid">
      <div class="sm:grid-cols-12">
        <h1 class="sm:hide">{{ estateName }}</h1>
        <div class="span-4 collection__intro mb-8x sm:mb-0">
          <p>Hier finden Sie sämtliche Informationen zu unserem Wohnungsangebot. Unter An-/Abmeldung haben Sie die Möglichkeit Ihre Rückmeldung direkt an uns zu richten. <strong>Achtung das Angebot ist nur 5 Tage gültig</strong>, wir bitten Sie um schnelle Rückmeldung. Die Vermietung erfolgt <strong>ohne</strong> Wohnungsbesichtigung.</p>
        </div>
        <div class="xs:hide span-5 collection__iso">
          <isometrie :estate="estateKey" :active="hovered" />
        </div>
        <div class="xs:hide span-3 flex justify-end">
          <a :href="estate.maps" target="_blank" title="Auf Google Maps anzeigen" class="link-maps">
            <span>Google Maps</span>
            <icon-link-external class="ml-1x" />
          </a>
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
          <list-item
            v-for="(label, key) in exteriors"
            :key="key"
            :class="'span-1 list-item-header line-after'">
            {{ label }}
            <a href="" @click.prevent="sort(`size_${key}`)">
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
          @mouseover="hovered = d.number" 
          @mouseleave="hovered = null">
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
          <list-item v-for="(label, key) in exteriors" :key="key" :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d['size_' + key] }} <span v-if="d['size_' + key] > 0">m<sup>2</sup></span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item']">
            <router-link :to="{name: 'collection-show', params: { uuid: uuid, itemUuid: d.uuid }}">
              {{ d.available_at }}
            </router-link>
          </list-item>
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
<script setup>
import { ref, computed, inject, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import NProgress from 'nprogress';
import http from '@/lib/http';
import { useSort } from '@/composables/useSort';
import SiteHeader from '@/views/frontend/layout/Header.vue';
import SiteMain from '@/views/frontend/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import Isometrie from '@/components/ui/misc/Isometrie.vue';
import IconSort from "@/components/ui/icons/Sort.vue";
import IconLinkExternal from '@/components/ui/icons/LinkExternal.vue';

const route = useRoute();
const estateName = inject('estate');
const estateKey = inject('estateKey');

// Highlighted in the isometry
const hovered = ref(null);

const uuid = ref(null);
const data = ref([]);
const estate = ref({});

// Columns of the list (key => label), the offer's estate
const exteriors = computed(() => estate.value.exteriors || {});
const isFetched = ref(false);
const isValid = ref(false);

const { sort, sortedData } = useSort(data);

onMounted(() => fetch());

function fetch() {
  NProgress.start();
  isFetched.value = false;
  http.get(`/api/user-collection/${route.params.uuid}`).then(response => {
    data.value = response.data.items;
    uuid.value = response.data.uuid;
    estate.value = response.data.estate;
    isValid.value = response.data.valid;
    isFetched.value = true;
    NProgress.done();
  });
}
</script>
