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
                      <div class="span-2 start-3">{{ apartment.building.city }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Bezeichnung</label></div>
                      <div class="span-2">{{ apartment.description }}</div>
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
            <h2 class="mt-15x">Angeboten</h2>
          </div>
        </apartment-grid>
      </apartment-wrapper>
    </form>
  </site-main>
</div>
</template>
<script>
import NProgress from 'nprogress';
import ErrorHandling from "@/mixins/ErrorHandling";
import IconReplace from "@/components/ui/icons/Replace.vue";
import IconTrash from "@/components/ui/icons/Trash-sm.vue";
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import SiteHeader from '@/views/layout/Header.vue';
import SiteMain from '@/views/layout/Main.vue';
import PageMenu from '@/views/pages/apartment/components/Menu.vue';
import ApartmentWrapper from '@/views/pages/apartment/components/Wrapper.vue';
import ApartmentGrid from '@/views/pages/apartment/components/Grid.vue';
import ApartmentRow from '@/views/pages/apartment/components/Row.vue';
import ApartmentLabel from '@/views/pages/apartment/components/Label.vue';
import ApartmentInput from '@/views/pages/apartment/components/Input.vue';
import Isometrie from '@/views/pages/apartment/components/Isometrie.vue';

export default {
  components: {
    NProgress,
    DialogWrapper,
    IconReplace,
    IconTrash,
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
        }
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
  },

};
</script>
