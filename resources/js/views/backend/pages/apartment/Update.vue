<template>
<div>
  <site-header 
    :user="$store.state.user" 
    :view="'show'">
  </site-header>
  <site-main v-if="isFetched">
    <page-menu 
      :uuid="$route.params.uuid"
      :apartment="apartment" 
      class="has-selection mb-20x"
    ></page-menu>
    <form @submit.prevent="submit" v-if="isFetched">
      <div class="apartment-action">
        <div>
          <div class="span-4 start-3">
            <button 
              type="submit" 
              :class="[hasErrors ? 'btn-primary is-small disabled' : 'btn-primary is-small']">
              Speichern
            </button>
          </div>
          <div class="span-4">
            <router-link 
              :to="{name: 'apartment-show', params: { uuid: apartment.uuid }}"
              class="btn-secondary is-outline is-small">
              Abbrechen
            </router-link>
          </div>
        </div>
      </div>
      <apartment-wrapper>
        <apartment-grid>
          <div class="span-6 line-after">
            <h2>{{ apartment.description }}&nbsp;&nbsp;<em>{{ apartment.room.description }} {{ apartment.size }} M<sup>2</sup></em></h2>
            <apartment-row>
              <div class="span-4">
                <h3>Grundriss</h3>
                <figure class="apartment-floorplan">
                  <img :src="`/assets/media/${apartment.number}.svg`" height="600" width="600" class="is-responsive">
                </figure>
              </div>
            </apartment-row>
            <apartment-row class="mt-15x">
              <div class="span-4">
                <h3>Lage / Details</h3>
                <div class="grid-cols-12">
                  <div class="span-6">
                    <apartment-row>
                      <div class="span-2"><label>Adresse</label></div>
                      <div class="span-2">{{ apartment.building.street }}</div>
                      <div class="span-2 start-3">{{ apartment.estate.city }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Bezeichnung</label></div>
                      <div class="span-2">{{ apartment.description }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Miete Netto</label></div>
                      <div class="span-2">{{ apartment.rent_net }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Nebenkosten</label></div>
                      <div class="span-2">{{ apartment.additional_cost }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Miete Brutto</label></div>
                      <div class="span-2">{{ apartment.rent_gross }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Kellerabteil</label></div>
                      <div class="span-2">vorhanden</div>
                    </apartment-row>
                    <apartment-row v-if="apartment.size_patio > 0">
                      <div class="span-2"><label>Sitzplatz</label></div>
                      <div class="span-2">{{ apartment.size_patio }} m<sup>2</sup></div>
                    </apartment-row>
                    <apartment-row v-if="apartment.size_terrace > 0">
                      <div class="span-2"><label>Terrasse</label></div>
                      <div class="span-2">{{ apartment.size_terrace }} m<sup>2</sup></div>
                    </apartment-row>
                    <apartment-row v-if="apartment.size_balcony > 0">
                      <div class="span-2"><label>Balkon</label></div>
                      <div class="span-2">{{ apartment.size_balcony }} m<sup>2</sup></div>
                    </apartment-row>
                  </div>
                  <div class="span-6">
                    <isometrie :active="apartment.number" />
                  </div>
                </div>
              </div>
            </apartment-row>
          </div>
          <div class="span-4">
            <h2>Hauptmieter*in</h2>
            <apartment-row>
              <apartment-label :cls="'span-1'">Name</apartment-label>
              <apartment-input :cls="'span-3'">
                <input type="text" v-model="apartment.tenant.name">
              </apartment-input>
            </apartment-row>
            <apartment-row>
              <apartment-label :cls="'span-1'">Vorname</apartment-label>
              <apartment-input :cls="'span-3'">
                <input type="text" v-model="apartment.tenant.firstname">
              </apartment-input>
            </apartment-row>
            <apartment-row>
              <apartment-label :cls="'span-1'">E-Mail</apartment-label>
              <apartment-input :cls="'span-3'">
                <input type="text" v-model="apartment.tenant.email">
              </apartment-input>
            </apartment-row>
            <apartment-row>
              <apartment-label :cls="'span-1'">Telefon</apartment-label>
              <apartment-input :cls="'span-3'">
                <input type="text" v-model="apartment.tenant.phone">
              </apartment-input>
            </apartment-row>
            <apartment-row>
              <apartment-label :cls="'span-1'">Status</apartment-label>
              <div class="span-3 flex">
                <a href="" class="icon-state flex" @click.prevent="setState(1)">
                  <icon-radio :active="apartment.state_id == 1 ? true : false" />
                  <span class="ml-2x">frei</span>
                </a>
                <a href="" class="icon-state flex ml-6x" @click.prevent="setState(3)">
                  <icon-radio :active="apartment.state_id == 3 ? true : false" />
                  <span class="ml-2x">vermietet</span>
                </a>
              </div>
            </apartment-row>
          </div>
        </apartment-grid>
      </apartment-wrapper>
    </form>
  </site-main>
</div>
</template>
<script>
import NProgress from 'nprogress';
import ErrorHandling from '@/mixins/ErrorHandling';
import IconRadio from "@/components/ui/icons/Radio.vue";
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import PageMenu from '@/components/ui/apartment/Menu.vue';
import ApartmentWrapper from '@/components/ui/apartment/Wrapper.vue';
import ApartmentGrid from '@/components/ui/apartment/Grid.vue';
import ApartmentRow from '@/components/ui/apartment/Row.vue';
import ApartmentLabel from '@/components/ui/apartment/Label.vue';
import ApartmentInput from '@/components/ui/apartment/Input.vue';
import Isometrie from '@/components/ui/misc/Isometrie.vue';

export default {
  components: {
    NProgress,
    DialogWrapper,
    IconRadio,
    SiteHeader,
    SiteMain,
    PageMenu,
    ApartmentWrapper,
    ApartmentGrid,
    ApartmentRow,
    ApartmentLabel,
    ApartmentInput,
    Isometrie
  },

  mixins: [ErrorHandling],

  data() {
    return {
      
      // Model
      apartment: {
        tenant: {
          name: null,
          firstname: null
        },
        state_id: 1,
      },

      // Routes
      routes: {
        fetch: '/api/apartment',
        put: '/api/apartment',
      },

      // States
      isFetched: false,
      isLoading: false,
      hasErrors: false,

      // Messages
      messages: {
        updated: 'Änderungen gespeichert!',
      },
    };
  },

  created() {
    this.fetch();
    NProgress.configure({ showBar: false });
  },

  methods: {

    fetch() {
      NProgress.start();
      this.isFetched = false;
      this.axios.get(`${this.routes.fetch}/${this.$route.params.uuid}`).then(response => {
        this.apartment = response.data;
        this.isFetched = true;
        NProgress.done();
      });
    },

    submit() {
      NProgress.start();
      this.isFetched = true;
      this.axios.put(`${this.routes.put}/${this.$route.params.uuid}`, this.apartment).then(response => {
        this.$router.push({ name: 'apartment-show', params: {uuid: this.apartment.uuid} });
        this.isFetched = false;
        NProgress.done();
      });
    },

    validate(event) {
      if (event.target.value.length > 0) {
        event.target.classList.remove('is-invalid');
        this.hasErrors = false;
        return;
      }
      event.target.classList.add('is-invalid');
      this.hasErrors = true;
    },

    setState(stateId) {
      this.apartment.state_id = stateId;
    }
  },

};
</script>
