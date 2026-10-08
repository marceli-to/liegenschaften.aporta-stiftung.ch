<template>
  <div>
    <site-header 
      :view="'tenants'">
      <nav class="selector" v-if="store.ui.hasSearch">
        <div>
          <div class="grid-cols-12">
            <div class="span-6">
              <h2>Mieter-Suche</h2>
              <input type="text" class="search" v-model="searchTerm" placeholder="Suche nach Vorname, Name, E-Mail, Telefon, Strasse">
            </div>
          </div>
        </div>
        <a href="javascript:;" :class="[store.filter.set ? 'is-active' : '', 'btn-primary is-filter']" @click.prevent="fetch()">Suchen</a>
        <a href="javascript:;" class="btn-secondary is-outline" @click.prevent="resetSearch()">Zurücksetzen</a>
      </nav>
    </site-header>
    <site-main v-if="isFetched">
      <div class="my-6x pr-6x w-full align-right">
        <a :href="`/export/mieter?v=${randomString()}`" target="_blank" class="link-export">
          Export Excel
          <icon-document class="ml-1x" />
        </a>
      </div>
      <list class="mt-6x" v-if="sortedData.length">
        <list-header>
          <list-item :class="'span-2 list-item-header line-after'">
            Vorname, Name
            <a href="" @click.prevent="sort('name')">
              <icon-sort />
            </a>
          </list-item>
          <list-item :class="'span-2 list-item-header line-after'">
            E-Mail
            <a href="" @click.prevent="sort('email')">
              <icon-sort />
            </a>
          </list-item>
          <list-item :class="'span-2 list-item-header line-after'">
            Telefon
          </list-item>
          <list-item :class="'span-3 list-item-header'">
            Addresse
            <a href="" @click.prevent="sort('apartment.building.street')">
              <icon-sort />
            </a>
          </list-item>
          <list-item :class="'span-3 list-item-header'">
            Wohnung
            <a href="" @click.prevent="sort('apartment.number')">
              <icon-sort />
            </a>
          </list-item>
        </list-header>
        <div 
          v-for="(d, index) in sortedData" 
          class="list-row no-hover" 
          :data-uuid="d.uuid"
          :key="d.id">
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 0, referrer: 'tenants' }}">
              <span>{{ d.firstname }} {{ d.name }}</span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 0, referrer: 'tenants' }}">
              <span>{{ d.email }}</span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 0, referrer: 'tenants' }}">
              <span>{{ d.phone }}</span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-3 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 0, referrer: 'tenants' }}">
              <span>{{ d.apartment.building.street }}</span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-3 list-item']">
            <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 0, referrer: 'tenants' }}">
              <span>{{ d.apartment.number }} / {{ d.apartment.description }}</span>
            </router-link>
          </list-item>
        </div>
      </list>
      <list-empty class="mt-6x text-md" v-else>
        {{messages.emptyData}}
      </list-empty>
    </site-main>

  </div>
  </template>
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import NProgress from 'nprogress';
import http from '@/lib/http';
import { store } from '@/store';
import { randomString } from '@/lib/utils';
import { useSort } from '@/composables/useSort';
import IconSort from "@/components/ui/icons/Sort.vue";
import IconDocument from "@/components/ui/icons/Document.vue";
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import ListEmpty from "@/components/ui/layout/ListEmpty.vue";

const data = ref([]);
const searchTerm = ref('');
const isFetched = ref(false);

const routes = {
  get: '/api/tenants',
};

const messages = {
  emptyData: 'Sorry, es sind keine Datensätze vorhanden.',
};

const { sort, sortedData } = useSort(data);

function onEnter(e) {
  if (e.key === 'Enter' && store.ui.hasSearch && searchTerm.value !== '') {
    fetch();
  }
}

onMounted(() => {
  fetch();
  document.addEventListener('keydown', onEnter);
});

onBeforeUnmount(() => document.removeEventListener('keydown', onEnter));

function fetch() {
  NProgress.start();
  isFetched.value = false;
  http.get(`${routes.get}/${searchTerm.value}`).then(response => {
    data.value = response.data.data;
    isFetched.value = true;
    store.ui.hasSearch = false;
    NProgress.done();
  });
}

function resetSearch() {
  store.ui.hasSearch = false;
  searchTerm.value = '';
  fetch();
}
</script>
