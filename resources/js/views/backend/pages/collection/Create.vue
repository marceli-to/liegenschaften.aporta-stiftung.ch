<template>
<div>
  <site-header></site-header>
  <site-main v-if="isFetched">
    <list v-if="sortedData.length">
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
          <a href="" @click.prevent="sort('size')">
            <icon-sort />
          </a>
        </list-item>
        <list-item
          v-for="(label, key, i) in exteriors"
          :key="key"
          :class="['span-1 list-item-header', i < Object.keys(exteriors).length - 1 ? 'line-after' : '']">
          {{ label }}
          <a href="" @click.prevent="sort(`size_${key}`)">
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
        class="list-row" 
        :key="apartment.uuid">
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-action']">
          <a href="" @click.prevent="remove(apartment.uuid)" v-if="isInCollection(apartment.uuid)">
           <icon-checkbox :active="'true'" class="icon icon-secondary" />
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
        <list-item v-for="(label, key) in exteriors" :key="key" :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}">
            {{ apartment['size_' + key] }} <span v-if="apartment['size_' + key] > 0">m<sup>2</sup></span>
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
    <form @submit.prevent class="collection__form" v-if="sortedData.length">
      <nav :class="[!isValid ? 'is-disabled' : '', 'page-menu page-menu__collection']">
        <ul>
          <li class="start-4">
            <a href="" @click.prevent="resetCandidates()">
              <icon-reset />
              <span>Zurücksetzen</span>
            </a>
          </li>
          <li>
            <a href="" @click.prevent="showStoreConfirm()">
              <icon-arrow-right :size="'md'" />
              <span>Senden</span>
            </a>
          </li>
        </ul>
        <div class="flex justify-center mt-6x">
          <a href="" @click.prevent="addCandidate()">
            <icon-plus class="icon" />
          </a>
        </div>
      </nav>
      <list class="mt-8x">
        <list-header class="is-narrow">
          <list-item :class="'span-2 start-3 list-item-header line-after'">Anrede</list-item>
          <list-item :class="'span-2 list-item-header line-after'">Vorname</list-item>
          <list-item :class="'span-2 list-item-header line-after'">Nachname</list-item>
          <list-item :class="'span-2 list-item-header'">E-Mail</list-item>
        </list-header>
        <list-row 
          v-for="(candidate, index) in candidates"
          :key="index"
          class="no-hover is-narrow mb-5x">
          <list-item class="span-2 start-3 list-item is-first line-after">
            <input type="text" v-model="candidate.salutation" required @blur="validate($event, candidate)">
          </list-item>
          <list-item class="span-2 list-item is-first line-after">
            <input type="text" v-model="candidate.firstname" required @blur="validate($event, candidate)">
          </list-item>
          <list-item class="span-2 list-item is-first line-after">
            <input type="text" v-model="candidate.name" required @blur="validate($event, candidate)">
          </list-item>
          <list-item class="span-2 list-item is-first">
            <input type="email" v-model="candidate.email" required @blur="validate($event, candidate)">
          </list-item>
        </list-row>
      </list>
      <div class="flex justify-center mt-6x" v-if="candidates.length > 1">
        <a href="" @click.prevent="removeCandidate()">
          <icon-trash class="icon-trash" />
        </a>
      </div>
      <list class="mt-8x">
        <list-row class="no-hover">
          <list-item :class="'span-8 start-3 mb-5x list-item-header'">Freitext</list-item>
          <list-item class="span-8 start-3 list-item is-first">
            <textarea v-model="remarks" class="textarea"></textarea>
          </list-item>
        </list-row>
      </list>
    </form>
  </site-main>
  <dialog-wrapper ref="dialogStoreConfirm">
    <template #message>
      <div>
        <strong>
          Möchten Sie die ausgewählten Angebote an {{ candidateList }} senden?
        </strong>
      </div>
    </template>
    <template #actions>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="submit()">Senden</a>
    </template>
  </dialog-wrapper>
  <dialog-wrapper ref="dialogStoreSuccess">
    <template #message>
      <div>
        <strong>
          Die ausgewählten Angebote wurden an an den/die Empfänger:in versendet.
        </strong>
      </div>
    </template>
    <template #button>
      <router-link :to="{name: 'apartments'}" class="btn-primary mb-3x">
        Schliessen
      </router-link>
    </template>
  </dialog-wrapper>
</div>
</template>
<script setup>
import { ref, inject, onMounted } from 'vue';
import NProgress from 'nprogress';
import http from '@/lib/http';
import { store } from '@/store';
import { useSort } from '@/composables/useSort';
import { useCollection } from '@/composables/useCollection';
import { useCandidates } from '@/composables/useCandidates';
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import IconSort from "@/components/ui/icons/Sort.vue";
import IconState from "@/components/ui/icons/State.vue";
import IconPlus from "@/components/ui/icons/Plus.vue";
import IconTrash from "@/components/ui/icons/Trash.vue";
import IconReset from "@/components/ui/icons/Reset.vue";
import IconCheckbox from "@/components/ui/icons/Checkbox.vue";
import IconArrowRight from "@/components/ui/icons/ArrowRight.vue";
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListRow from "@/components/ui/layout/ListRow.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import ListEmpty from "@/components/ui/layout/ListEmpty.vue";

// Columns of the list (key => label)
const exteriors = inject('exteriors');

const data = ref([]);
const remarks = ref(null);
const isFetched = ref(false);
const dialogStoreConfirm = ref(null);
const dialogStoreSuccess = ref(null);

const routes = {
  get: '/api/apartments',
  post: '/api/collection'
};

const messages = {
  emptyData: 'Es sind noch keine Daten vorhanden...',
};

const { sort, sortedData } = useSort(data);
const { resetCollection, removeFromCollection, isInCollection } = useCollection();
const { candidates, isValid, addCandidate, removeCandidate, resetCandidates, validate, candidateList } = useCandidates();

onMounted(() => {
  store.ui.hasCollection = true;
  get();
});

function get() {
  NProgress.start();
  isFetched.value = false;
  http.post(routes.get, store.collection).then(response => {
    data.value = response.data.data;
    isFetched.value = true;
    NProgress.done();
  });
}

// Unpick an apartment and show the list without it
function remove(uuid) {
  removeFromCollection(uuid);
  get();
}

function submit() {
  dialogStoreConfirm.value.hide();
  const data = {
    candidates: candidates.value,
    remarks: remarks.value ? remarks.value : null,
    items: store.collection.items
  };

  NProgress.start();
  http.post(routes.post, data).then(() => {
    resetCollection();
    resetCandidates();
    dialogStoreSuccess.value.show();
    NProgress.done();
  });
}

function showStoreConfirm() {
  dialogStoreConfirm.value.show();
}
</script>
