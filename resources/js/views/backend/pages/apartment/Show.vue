<template>
<div>
  <site-header 
    :user="$store.state.user" 
    :view="'show'">
  </site-header>
  <site-main v-if="isFetched">
    <page-menu 
      :id="$route.params.uuid"
      :apartment="apartment" 
    ></page-menu>
    <apartment-wrapper>
      <apartment-grid>
        <div class="span-6 line-after">
          <h2>{{ apartment.description }}&nbsp;&nbsp;<em>{{ apartment.room.description }} {{ apartment.size }} M<sup>2</sup></em></h2>
          <apartment-row>
            <div class="span-4 is-first">
              <h3>Grundriss</h3>
              <figure class="apartment-floorplan">
                <img :src="`/assets/media/${apartment.number}.svg`" height="600" width="600" class="is-responsive">
              </figure>
            </div>
          </apartment-row>
          <apartment-row class="mt-15x">
            <div class="span-4 is-first">
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
                    <div class="span-2"><label>Nummer</label></div>
                    <div class="span-2">{{ apartment.number }}</div>
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
            <div class="span-1 is-first"><label>Name</label></div>
            <div class="span-3 is-first">{{ apartment.tenant_id ? apartment.tenant.name : '–' }}</div>
          </apartment-row>
          <apartment-row>
            <div class="span-1"><label>Vorname</label></div>
            <div class="span-3">{{ apartment.tenant_id ? apartment.tenant.firstname : '–' }}</div>
          </apartment-row>
          <apartment-row>
            <div class="span-1"><label>E-Mail</label></div>
            <div class="span-3">{{ apartment.tenant_id && apartment.tenant.email ? apartment.tenant.email : '–' }}</div>
          </apartment-row>
          <apartment-row>
            <div class="span-1"><label>Telefon</label></div>
            <div class="span-3">{{ apartment.tenant_id && apartment.tenant.phone ? apartment.tenant.phone : '–' }}</div>
          </apartment-row>
          <h2 class="mt-12x">Informationen</h2>
          <apartment-row>
            <div class="span-1"><label>Status</label></div>
            <div class="span-3" v-if="apartment.state_id == 1">frei</div>
            <div class="span-3" v-else-if="apartment.state_id == 3">reserviert</div>
          </apartment-row>
          <apartment-row>
            <div class="span-1"><label>Bezugstermin</label></div>
            <div class="span-3">{{ apartment.available_at }}</div>
          </apartment-row>

          <template v-if="apartment.collection_items.length">
            <h2 class="mt-15x">Angeboten</h2>
            <apartment-row-header>
              <div class="span-2">Name</div>
              <div class="span-1">Datum</div>
              <div class="span-1 align-center">Interesse</div>
            </apartment-row-header>
            <apartment-row
              v-for="item in apartment.collection_items" 
              :key="item.uuid">
              <div class="span-2 border-light-blue">{{ item.collection.firstname }} {{ item.collection.name }}</div>
              <div class="span-1">{{ item.sent_at }}</div>
              <div class="span-1 flex justify-center text-primary">
                <icon-checkmark v-if="item.accepted" />
                <icon-cross :size="'md'" v-else />
              </div>
            </apartment-row>
          </template>
        </div>
      </apartment-grid>
    </apartment-wrapper>
  </site-main>

</div>
</template>
<script>
import NProgress from 'nprogress';
import Filter from "@/views/backend/pages/mixins/Filter";
import ErrorHandling from '@/mixins/ErrorHandling';
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import PageMenu from '@/components/ui/apartment/Menu.vue';
import ApartmentWrapper from '@/components/ui/apartment/Wrapper.vue';
import ApartmentGrid from '@/components/ui/apartment/Grid.vue';
import ApartmentRow from '@/components/ui/apartment/Row.vue';
import ApartmentRowHeader from '@/components/ui/apartment/RowHeader.vue';
import ApartmentLabel from '@/components/ui/apartment/Label.vue';
import ApartmentInput from '@/components/ui/apartment/Input.vue';
import Isometrie from '@/components/ui/misc/Isometrie.vue';
import IconCross from "@/components/ui/icons/Cross.vue";
import IconCheckmark from '@/components/ui/icons/Checkmark.vue';

export default {
  components: {
    NProgress,
    SiteHeader,
    SiteMain,
    PageMenu,
    ApartmentWrapper,
    ApartmentGrid,
    ApartmentRow,
    ApartmentInput,
    ApartmentLabel,
    ApartmentRowHeader,
    Isometrie,
    IconCross,
    IconCheckmark
  },

  mixins: [ErrorHandling, Filter],

  data() {
    return {
      
      // Model
      apartment: {
        number: null,
      },

      // Routes
      routes: {
        fetch: '/api/apartment',
      },

      // States
      isFetched: false,
      isLoading: false,
      hasErrors: false,
    };
  },

  mounted() {
    this.fetch();
    NProgress.configure({ showBar: false });
  },

  methods: {

    fetch() {
      this.isFetched = false;
      NProgress.start();
      this.axios.get(`${this.routes.fetch}/${this.$route.params.uuid}`).then(response => {
        this.apartment = response.data;
        this.isFetched = true;
        this.updateFilterMenu(this.$route.params.uuid);
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

  watch: {
    '$route'() {
      this.fetch();
    }
  }

};
</script>
