<template>
<div>
  <site-header></site-header>
  <site-main v-if="isMounted">
    <page-menu 
      :id="$route.params.uuid"
      :apartment="collectionItem.apartment" 
    ></page-menu>
    <apartment-wrapper>
      <apartment-grid>
        <div class="span-6 line-after">
          <h2>{{ collectionItem.apartment.description }}&nbsp;&nbsp;<em>{{ collectionItem.apartment.room.description }} {{ collectionItem.apartment.size }} M<sup>2</sup></em></h2>
          <apartment-row>
            <div class="span-4 is-first">
              <h3>Grundriss</h3>
              <figure class="apartment-floorplan">
                <img :src="`/assets/media/${collectionItem.apartment.number}.svg`" height="600" width="600" class="is-responsive">
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
                    <div class="span-2">{{ collectionItem.apartment.building.street }}</div>
                    <div class="span-2 start-3">{{ collection.estate.city }}</div>
                  </apartment-row>
                  <apartment-row>
                    <div class="span-2"><label>Bezeichnung</label></div>
                    <div class="span-2">{{ collectionItem.apartment.description }}</div>
                  </apartment-row>
                  <apartment-row v-if="collectionItem.apartment.size_patio > 0">
                    <div class="span-2"><label>Sitzplatz</label></div>
                    <div class="span-2">{{ collectionItem.apartment.size_patio }} m<sup>2</sup></div>
                  </apartment-row>
                  <apartment-row v-if="collectionItem.apartment.size_terrace > 0">
                    <div class="span-2"><label>Terrasse</label></div>
                    <div class="span-2">{{ collectionItem.apartment.size_terrace }} m<sup>2</sup></div>
                  </apartment-row>
                  <apartment-row v-if="collectionItem.apartment.size_balcony > 0">
                    <div class="span-2"><label>Balkon</label></div>
                    <div class="span-2">{{ collectionItem.apartment.size_balcony }} m<sup>2</sup></div>
                  </apartment-row>
                </div>
                <div class="span-6">
                  <isometrie :active="collectionItem.apartment.number" />
                </div>
              </div>
            </div>
          </apartment-row>
        </div>
        <div class="span-4">

        </div>
      </apartment-grid>
    </apartment-wrapper>
  </site-main>

</div>
</template>
<script>
import NProgress from 'nprogress';
import ErrorHandling from '@/mixins/ErrorHandling';
import SiteHeader from '@/views/frontend/layout/Header.vue';
import SiteMain from '@/views/frontend/layout/Main.vue';
import PageMenu from '@/views/frontend/components/ui/Menu.vue';
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

  mixins: [ErrorHandling],

  data() {
    return {
      
      // Collection
      collection: {},

      // Collection item
      collectionItem: {},

      // Routes
      routes: {
        fetch: '/api/apartment',
      },

      // States
      isMounted: false,
    };
  },

  mounted() {
    NProgress.configure({ showBar: false });
    this.collection = this.$parent.$props.collection;

    // find item in collection by route param (uuid)
    const itemUuid = this.$route.params.itemUuid;
    const index = this.collection.items.findIndex(item => item.uuid == itemUuid);
    if (index > -1) {
      this.collectionItem = this.collection.items[index];
      this.isMounted = true;
    }
  },

  methods: {
  },


};
</script>
