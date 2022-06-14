<template>
<div>
  <site-header :user="$store.state.user"></site-header>
  <site-main v-if="isFetched">
    <list v-if="sortedData.length">
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
          <a href="" @click.prevent="removeFromCollection(apartment.uuid, true)" v-if="isInCollection(apartment.uuid)">
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
      <nav :class="[!isValid ? 'is-disabled' : '', 'page-menu page-menu__collection']">
        <ul>
          <li class="start-4">
            <a href="">
              <icon-cross :size="'md'" />
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
          <list-item :class="'span-2 start-4 list-item-header line-after'">Vorname</list-item>
          <list-item :class="'span-2 list-item-header line-after'">Nachname</list-item>
          <list-item :class="'span-2 list-item-header'">E-Mail</list-item>
        </list-header>
        <list-row 
          v-for="(candidate, index) in candidates"
          :key="index"
          class="no-hover is-narrow mb-5x">
          <list-item class="span-2 start-4 list-item is-first line-after">
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
          <icon-trash class="icon" />
        </a>
      </div>
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
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="store()">Senden</a>
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
<script>
import NProgress from 'nprogress';
import ErrorHandling from '@/mixins/ErrorHandling';
import Helpers from "@/mixins/Helpers";
import Sort from "@/mixins/Sort";
import Filter from "@/views/backend/pages/mixins/Filter";
import Collection from "@/views/backend/pages/mixins/Collection";
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import IconSort from "@/components/ui/icons/Sort.vue";
import IconState from "@/components/ui/icons/State.vue";
import IconRadio from "@/components/ui/icons/Radio.vue";
import IconPlus from "@/components/ui/icons/Plus.vue";
import IconTrash from "@/components/ui/icons/Trash.vue";
import IconCross from "@/components/ui/icons/Cross.vue";
import IconArrowRight from "@/components/ui/icons/ArrowRight.vue";
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListRow from "@/components/ui/layout/ListRow.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import ListAction from "@/components/ui/layout/ListAction.vue";
import ListEmpty from "@/components/ui/layout/ListEmpty.vue";

export default {

  components: {
    NProgress,
    SiteHeader,
    SiteMain,
    DialogWrapper,
    IconSort,
    IconState,
    IconRadio,
    IconPlus,
    IconTrash,
    IconCross,
    IconArrowRight,
    List,
    ListRow,
    ListHeader,
    ListItem,
    ListAction,
    ListEmpty,
  },
 
  mixins: [ErrorHandling, Helpers, Sort, Filter, Collection],

  data() {
    return {

      // Data
      data: [],

      // Candidates
      candidates: [
        {
          name: 'Marcel',
          firstname: 'Stadelmann',
          email: 'm@marceli.to'
        },
      ],

      // Routes
      routes: {
        get: '/api/apartments',
        post: '/api/collection'
      },

      // States
      isFetched: false,
      isValid: false,
      hasErrors: false,

      // Messages
      messages: {
        emptyData: 'Es sind noch keine Daten vorhanden...',
        updated: 'Status geändert',
      },
    };
  },

  mounted() {
    NProgress.configure({ showBar: false });
    this.hasCollection = true;
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

    store() {
      this.$refs.dialogStoreConfirm.hide();
      const data = {
        candidates: this.candidates,
        items: this.$store.state.collection.items
      };
      
      NProgress.start();
      this.axios.post(`${this.routes.post}`, data).then(response => {
        this.reset();
        this.showStoreSuccess();
        NProgress.done();
      });
    },

    reset() {
      this.resetCollection();
      this.resetCandidates();
    },

    addCandidate() {
      this.candidates.push({
        name: null,
        firstname: null,
        email: null,
      });
      this.isValid = false;
    },

    removeCandidate() {
      this.candidates.pop();
      this.isValid = true;
    },

    resetCandidates() {
      this.candidates = [
        {
          name: null,
          firstname: null,
          email: null,
        },
      ]
    },

    validate(event, candidate) {

      if (this.validateRequired(candidate.name) && this.validateRequired(candidate.firstname) && this.validateEmail(candidate.email)) {
        event.target.classList.remove('is-invalid');
        this.isValid = true;
        return true;
      }
      else {
        if (event.target.type == 'email' && this.validateEmail(event.target.value)) {
          event.target.classList.remove('is-invalid');
          return;
        }
        if (event.target.type == 'text' && this.validateRequired(event.target.value)) {
          event.target.classList.remove('is-invalid');
          return;
        }
      }
      event.target.classList.add('is-invalid');
      this.isValid = false;
      return;
    },

    showStoreConfirm() {
      this.$refs.dialogStoreConfirm.show();
    },

    showStoreSuccess() {
      this.$refs.dialogStoreSuccess.show();
    },
  },

  computed: {
    candidateList() {
      if (this.candidates.length == 1) {
        return this.candidates[0].email;
      }
      
      if (this.candidates.length == 2) {
        return Object.keys(this.candidates).map(index => `${this.candidates[index].email}`).join(" und ");
      }
      
      if (this.candidates.length > 2) {
        let candidates = this.candidates;
        const last_candidate = candidates.pop();
        const candidate_list = Object.keys(this.candidates).map(index => `${this.candidates[index].email}`).join(", ");
        return `${candidate_list} und ${last_candidate.email}`;
      }
    }
  },
}
</script>