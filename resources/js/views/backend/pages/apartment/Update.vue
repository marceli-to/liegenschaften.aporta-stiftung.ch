<template>
<div>
  <site-header 
    :view="'show'">
  </site-header>
  <site-main v-if="isFetched">
    <page-menu 
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
                  <img :src="`/assets/media/${apartment.number}-${apartment.uuid}.svg`" height="600" width="600" class="is-responsive">
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
                      <div class="span-2"><label>Lage</label></div>
                      <div class="span-2">{{ apartment.description }}</div>
                    </apartment-row>
                    <apartment-row>
                      <apartment-label :cls="'span-2'">Miete Netto *</apartment-label>
                      <apartment-input :cls="'span-2'">
                        <input type="text" v-model="apartment.rent_net" @blur="validate($event, apartment)">
                      </apartment-input>
                    </apartment-row>
                    <apartment-row>
                      <apartment-label :cls="'span-2'">Nebenkosten *</apartment-label>
                      <apartment-input :cls="'span-2'">
                        <input type="text" v-model="apartment.additional_cost" @blur="validate($event, apartment)">
                      </apartment-input>
                    </apartment-row>
                    <apartment-row>
                      <apartment-label :cls="'span-2'">Miete Brutto *</apartment-label>
                      <apartment-input :cls="'span-2'">
                        <input type="text" v-model="apartment.rent_gross" @blur="validate($event, apartment)">
                      </apartment-input>
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
                    <apartment-row v-if="apartment.size_loggia > 0">
                      <div class="span-2"><label>Loggia</label></div>
                      <div class="span-2">{{ apartment.size_loggia }} m<sup>2</sup></div>
                    </apartment-row>
                  </div>
                  <div class="span-6">
                    <isometrie :estate="estateKey" :active="apartment.number" />
                  </div>
                </div>
              </div>
            </apartment-row>
          </div>
          <div class="span-4">
            <h2>Hauptmieter*in</h2>
            <apartment-row>
              <apartment-label :cls="'span-1'">Name *</apartment-label>
              <apartment-input :cls="'span-3'">
                <template v-if="apartment.tenant.uuid && !isEditTenant">
                  {{ apartment.tenant.name }}
                </template>
                <template v-else>
                  <input type="text" v-model="apartment.tenant.name">
                </template>
              </apartment-input>
            </apartment-row>
            <apartment-row>
              <apartment-label :cls="'span-1'">Vorname *</apartment-label>
              <apartment-input :cls="'span-3'">
                <template v-if="apartment.tenant.uuid && !isEditTenant">
                  {{ apartment.tenant.firstname }}
                </template>
                <template v-else>
                  <input type="text" v-model="apartment.tenant.firstname">
                </template>
              </apartment-input>
            </apartment-row>
            <apartment-row>
              <apartment-label :cls="'span-1'">E-Mail</apartment-label>
              <apartment-input :cls="'span-3'">
                <template v-if="apartment.tenant.uuid && !isEditTenant">
                  {{ apartment.tenant.email }}
                </template>
                <template v-else>
                  <input type="text" v-model="apartment.tenant.email">
                </template>
              </apartment-input>
            </apartment-row>
            <apartment-row>
              <apartment-label :cls="'span-1'">Telefon</apartment-label>
              <apartment-input :cls="'span-3'">
                <template v-if="apartment.tenant.uuid && !isEditTenant">
                  {{ apartment.tenant.phone ? apartment.tenant.phone : '–' }}
                </template>
                <template v-else>
                  <input type="text" v-model="apartment.tenant.phone">
                </template>
              </apartment-input>
            </apartment-row>
            <apartment-row v-if="apartment.tenant.uuid">
              <div class="span-2 border-none">
                <a href="javascript:;" class="btn-primary is-small" @click.prevent="editTenant()">
                  Mieter bearbeiten
                </a>
              </div>
              <div class="span-2 border-none">
                <a href="javascript:;" class="btn-secondary is-outline is-small" @click.prevent="removeTenant()">
                  Mieter entfernen
                </a>
              </div>
            </apartment-row>

            <h2 class="mt-12x">Informationen</h2>
            <apartment-row>
              <apartment-label :cls="'span-1'">Status</apartment-label>
              <div class="span-3 flex">
                <a href="" class="icon-state flex" @click.prevent="setState(1)">
                  <icon-radio :active="apartment.state_id == 1 ? true : false" />
                  <span class="ml-2x">frei</span>
                </a>
                <a href="" class="icon-state flex ml-6x" @click.prevent="setState(2)">
                  <icon-radio :active="apartment.state_id == 2 ? true : false" />
                  <span class="ml-2x">reserviert</span>
                </a>
                <a href="" class="icon-state flex ml-6x" @click.prevent="setState(3)">
                  <icon-radio :active="apartment.state_id == 3 ? true : false" />
                  <span class="ml-2x">vermietet</span>
                </a>
              </div>
            </apartment-row>
            <apartment-row>
              <apartment-label :cls="'span-1'">Bezugstermin</apartment-label>
              <apartment-input :cls="'span-3'">
                <input
                  type="text"
                  name="date"
                  :value="apartment.available_at"
                  @input="maskDate">
              </apartment-input>
            </apartment-row>
          </div>
        </apartment-grid>
      </apartment-wrapper>
    </form>
  </site-main>
</div>
</template>
<script setup>
import { ref, inject, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import NProgress from 'nprogress';
import http from '@/lib/http';
import IconRadio from "@/components/ui/icons/Radio.vue";
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import PageMenu from '@/components/ui/apartment/Menu.vue';
import ApartmentWrapper from '@/components/ui/apartment/Wrapper.vue';
import ApartmentGrid from '@/components/ui/apartment/Grid.vue';
import ApartmentRow from '@/components/ui/apartment/Row.vue';
import ApartmentLabel from '@/components/ui/apartment/Label.vue';
import ApartmentInput from '@/components/ui/apartment/Input.vue';
import Isometrie from '@/components/ui/misc/Isometrie.vue';

const route = useRoute();
const router = useRouter();
const estateKey = inject('estateKey');

const apartment = ref({
  tenant: {
    name: null,
    firstname: null
  },
  rent_net: null,
  additional_cost: null,
  rent_gross: null,
  state_id: 1,
});

const routes = {
  fetch: '/api/apartment',
  put: '/api/apartment',
};

const isFetched = ref(false);
const isEditTenant = ref(false);
const hasErrors = ref(false);

onMounted(() => fetch());

function fetch() {
  NProgress.start();
  isFetched.value = false;
  http.get(`${routes.fetch}/${route.params.uuid}`).then(response => {
    apartment.value = response.data;
    isFetched.value = true;
    NProgress.done();
  });
}

function submit() {
  NProgress.start();
  isFetched.value = true;
  if (hasErrors.value) {
    return;
  }
  http.put(`${routes.put}/${route.params.uuid}`, apartment.value).then(() => {
    router.push({ name: 'apartment-show', params: { uuid: apartment.value.uuid } });
    isFetched.value = false;
    NProgress.done();
  });
}

function validate(event) {
  if (event.target.value.length > 0) {
    event.target.classList.remove('is-invalid');
    hasErrors.value = false;
    return;
  }
  event.target.classList.add('is-invalid');
  hasErrors.value = true;
}

// dd.mm.yyyy while typing (was vue-the-mask «##.##.####»)
function maskDate(event) {
  const digits = event.target.value.replace(/\D/g, '').slice(0, 8);
  const masked = [digits.slice(0, 2), digits.slice(2, 4), digits.slice(4, 8)].filter(Boolean).join('.');
  event.target.value = masked;
  apartment.value.available_at = masked;
}

function setState(stateId) {
  apartment.value.state_id = stateId;
}

function editTenant() {
  isEditTenant.value = true;
}

function removeTenant() {
  apartment.value.tenant = {
    uuid: null,
    name: null,
    firstname: null,
    email: null,
    phone: null,
  };
}
</script>
