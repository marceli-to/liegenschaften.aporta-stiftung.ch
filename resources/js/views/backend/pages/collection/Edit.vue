<template>
  <div>
    <site-header :user="$store.state.user"></site-header>
    <site-main v-if="isFetched">
      <list v-if="data">
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
          v-for="(item, index) in data.items" 
          class="list-row" 
          :key="item.apartment.uuid">
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-action']">
            <icon-checkbox :active="'true'" class="icon icon-secondary" />
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}">
              {{ item.apartment.building.street }}
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}">
              {{ item.apartment.description }}
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}">
              {{ item.apartment.number }}
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}">
              {{ item.apartment.rent_gross }}
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}">
              {{ item.apartment.room.abbreviation }}
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}">
              {{ item.apartment.size }} m<sup>2</sup>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}">
              {{ item.apartment.size_terrace }} <span v-if="item.apartment.size_terrace > 0">m<sup>2</sup></span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}">
              {{ item.apartment.size_patio }} <span v-if="item.apartment.size_patio > 0">m<sup>2</sup></span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}">
              {{ item.apartment.size_balcony }} <span v-if="item.apartment.size_balcony > 0">m<sup>2</sup></span>
            </router-link>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-state']">
            <router-link :to="{name: 'apartment-show', params: { uuid: item.apartment.uuid }}" class="icon-state">
              <icon-state :id="item.apartment.state_id" />
            </router-link>
          </list-item>
        </div>
      </list>
      <list-empty v-else>
        {{messages.emptyData}}
      </list-empty>
      <form @submit.prevent="submit" class="collection__form" v-if="data">
        <nav :class="[!isValid ? 'is-disabled' : '', 'page-menu page-menu__collection']">
          <ul>
            <li class="start-4">
              <a href="" @click.prevent="resetCandidates()">
                <icon-reset />
                <span>Zurücksetzen</span>
              </a>
            </li>
            <li>
              <a href="" @click.prevent="showUpdateConfirm()">
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
    <dialog-wrapper ref="dialogUpdateConfirm">
      <template #message>
        <div>
          <strong>Möchten Sie die ausgewählten Angebote an {{ candidateList }} senden?</strong>
        </div>
      </template>
      <template #actions>
        <a href="javascript:;" class="btn-primary mb-3x" @click.stop="update()">Senden</a>
      </template>
    </dialog-wrapper>
    <dialog-wrapper ref="dialogStoreSuccess">
      <template #message>
        <div>
          <strong>Die ausgewählten Angebote wurden an an den/die Empfänger:in versendet.</strong>
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
  import IconReset from "@/components/ui/icons/Reset.vue";
  import IconCheckbox from "@/components/ui/icons/Checkbox.vue";
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
      IconCheckbox,
      IconReset,
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
        candidates: [],

        // Remarks
        remarks: null,
  
        // Routes
        routes: {
          fetch: '/api/collection',
          put: '/api/collection'
        },
  
        // States
        isFetched: false,
        isValid: true,
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
        this.axios.get(`${this.routes.fetch}/${this.$route.params.uuid}`).then(response => {
          this.data = response.data;
          this.remarks = this.data.remarks;
          this.candidates.push({
            uuid: this.data.uuid,
            salutation: this.data.salutation,
            name: this.data.name,
            firstname: this.data.firstname,
            email: this.data.email,
          });
          this.isFetched = true;
          NProgress.done();
        });
      },
  
      update() {
        this.$refs.dialogUpdateConfirm.hide();

        const data = {
          candidates: this.candidates,
          remarks: this.remarks,
          items: this.data.items.map(item => item.apartment.uuid),
        };
        
        NProgress.start();
        this.axios.put(`${this.routes.put}/${this.$route.params.uuid}`, data).then(response => {
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
            salutation: null,
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
  
      showUpdateConfirm() {
        this.$refs.dialogUpdateConfirm.show();
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