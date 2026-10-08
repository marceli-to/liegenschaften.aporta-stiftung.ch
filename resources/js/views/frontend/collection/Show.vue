<template>
<div >
  <site-header :view="'show'"></site-header>
  <site-main v-if="isFetched">
    <div v-if="isValid">
      <page-menu :pagination="pagination" :fileUuid="`${data.number}-${data.apartementUuid}`"></page-menu>
      <apartment-wrapper>
        <div class="sm:grid-cols-10 xs:hide">
          <div class="span-6">
            <h2>{{ data.description }}&nbsp;&nbsp;<em>{{ data.rooms }} {{ data.size }} M<sup>2</sup></em></h2>
          </div>
          <div class="span-4">
            Bezugstermin: <strong>{{ data.available_at }}</strong>
          </div>
        </div>
        <div class="sm:hide">
          <h2>{{ data.room_description }}, {{ data.size }} m<sup>2</sup><br><em>{{ data.street }}, {{ data.city }}<br>{{ data.description }}<br><br>Bezugstermin: <strong>{{ data.available_at }}</strong></em></h2>
        </div>
        
        <apartment-grid class="sm:grid-cols-10">
          <div class="span-6 sm:line-after">
            <apartment-row>
              <div class="span-4 is-first">
                <div class="xs:grid-cols-6">
                  <h3 class="xs:span-3">Grundriss</h3>
                  <div class="xs:span-3 sm:hide flex justify-end">
                    <a :href="`/assets/media/${data.number}-${data.apartementUuid}.pdf`" target="_blank" class="icon" title="Weitere Informationen (PDF)">
                      <span>Weitere Informationen (PDF)</span>
                    </a>
                  </div>
                </div>
                <figure class="apartment-floorplan">
                  <a :href="`/assets/media/${data.number}-${data.apartementUuid}.pdf`" target="_blank" title="Weitere Informationen (PDF)">
                    <img :src="`/assets/media/${data.number}-${data.apartementUuid}.svg`" height="600" width="600" class="is-responsive">
                  </a>
                </figure>
              </div>
            </apartment-row>
            <apartment-row class="mt-15x">
              <div class="span-4 is-first">
                <h3>Lage / Details</h3>
                <div class="grid-cols-12">
                  <div class="span-12 sm:span-6 mb-8x sm:mb-0">
                    <apartment-row>
                      <div class="span-2"><label>Adresse</label></div>
                      <div class="span-2">{{ data.street }}</div>
                      <div class="span-2 start-3">{{ data.city }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Lage</label></div>
                      <div class="span-2">{{ data.description }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Miete Netto</label></div>
                      <div class="span-2">{{ data.rent_net }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Nebenkosten</label></div>
                      <div class="span-2">{{ data.additional_cost }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Miete Brutto</label></div>
                      <div class="span-2">{{ data.rent_gross }}</div>
                    </apartment-row>
                    <apartment-row>
                      <div class="span-2"><label>Kellerabteil</label></div>
                      <div class="span-2">vorhanden</div>
                    </apartment-row>
                    <apartment-row v-if="data.size_patio > 0">
                      <div class="span-2"><label>Sitzplatz</label></div>
                      <div class="span-2">{{ data.size_patio }} m<sup>2</sup></div>
                    </apartment-row>
                    <apartment-row v-if="data.size_terrace > 0">
                      <div class="span-2"><label>Terrasse</label></div>
                      <div class="span-2">{{ data.size_terrace }} m<sup>2</sup></div>
                    </apartment-row>
                    <apartment-row v-if="data.size_balcony > 0">
                      <div class="span-2"><label>Balkon</label></div>
                      <div class="span-2">{{ data.size_balcony }} m<sup>2</sup></div>
                    </apartment-row>
                    <apartment-row v-if="data.size_loggia > 0">
                      <div class="span-2"><label>Loggia</label></div>
                      <div class="span-2">{{ data.size_loggia }} m<sup>2</sup></div>
                    </apartment-row>
                    <apartment-row v-if="data.shared_exterior">
                      <div class="span-2"><label>Aussenfläche</label></div>
                      <div class="span-2">gemeinsam an der Egligasse</div>
                    </apartment-row>
                  </div>
                  <div class="span-12 sm:span-6">
                    <isometrie :estate="estateKey" :active="data.number" />
                  </div>
                </div>
              </div>
            </apartment-row>
          </div>
          <div class="span-4">
            <apartment-row>
              <div class="span-4 is-first">
                <h3 v-if="!data.has_reply">An-/Abmeldung</h3>
                <h3 v-else>Ihre Rückmeldung</h3>
                <form>
                  <apartment-row class="pb-3x" v-if="!data.has_reply">
                    <apartment-label :cls="'span-3'">Ich habe Interesse an dieser Wohnung</apartment-label>
                    <apartment-input :cls="'span-1 flex justify-center'">
                      <a href="" @click.prevent="toggleAccept(1)" class="icon-state">
                        <icon-radio :active="form.accepted == 1" />
                      </a>
                    </apartment-input>
                  </apartment-row>
                  <apartment-row class="pb-3x" v-else-if="data.has_reply && data.accepted == 1">
                    <apartment-label :cls="'span-3'">Ich habe Interesse an dieser Wohnung</apartment-label>
                    <apartment-input :cls="'span-1 flex justify-center'">
                      <icon-radio class="icon" :active="true" />
                    </apartment-input>
                  </apartment-row>

                  <apartment-row class="pb-3x" v-if="!data.has_reply">
                    <apartment-label :cls="'span-3'">Ich habe <strong>kein</strong> Interesse an diesem Angebot, bleibe aber für eine Wohnung in einer anderen Siedlung auf der Warteliste</apartment-label>
                    <apartment-input :cls="'span-1 flex justify-center'">
                      <a href="" @click.prevent="toggleAccept(0)" class="icon-state">
                        <icon-radio :active="form.accepted == 0" />
                      </a>
                    </apartment-input>
                    <template v-if="form.accepted == 0">
                      <apartment-label :cls="'span-4 mt-1x'" style="border-top: none">
                        <span :class="[hasValidationErrors ? 'text-danger': '', '']">Teilen Sie uns bitte einen Grund mit:</span>
                        <div class="mt-1x md:mt-3x">
                          <textarea v-model="form.comment" :class="[hasValidationErrors ? 'is-invalid': '', '']" @focus="removeValidationError()"></textarea>
                        </div>
                      </apartment-label>
                    </template>
                  </apartment-row>

                  <apartment-row class="pb-3x" v-else-if="data.has_reply && data.accepted == 0">
                    <apartment-label :cls="'span-3'">
                      Ich habe <strong>kein</strong> Interesse an diesem Angebot, bleibe aber für eine Wohnung in einer anderen Siedlung auf der Warteliste
                      <div class="mt-2x"><strong>Begründung:</strong><br>{{ data.comment }}</div>
                    </apartment-label>
                    <apartment-input :cls="'span-1 flex justify-center'">
                      <icon-radio :active="true" />
                    </apartment-input>
                  </apartment-row>

                  <apartment-row class="pb-3x" v-if="!data.has_reply">
                    <apartment-label :cls="'span-3'">Ich bin nicht mehr auf Wohungssuche, bitte löschen Sie meine Anmeldung</apartment-label>
                    <apartment-input :cls="'span-1 flex justify-center'">
                      <a href="" @click.prevent="toggleAccept(2)" class="icon-state">
                        <icon-radio :active="form.accepted == 2" />
                      </a>
                    </apartment-input>
                  </apartment-row>
                  <apartment-row class="pb-3x" v-else-if="data.has_reply && data.accepted == 2">
                    <apartment-label :cls="'span-3'">Ich bin nicht mehr auf Wohungssuche, bitte löschen Sie meine Anmeldung</apartment-label>
                    <apartment-input :cls="'span-1 flex justify-center'">
                      <icon-radio :active="true" />
                    </apartment-input>
                  </apartment-row>

                  <apartment-row class="pb-3x" v-if="!data.has_reply && form.accepted == 1">
                    <apartment-label :cls="'span-3'">Ich habe Interesse an einem Abstellplatz in der Tiefgarage Eichbühlstrasse (Warteliste)</apartment-label>
                    <apartment-input :cls="'span-1 flex justify-center'">
                      <a href="" @click.prevent="toggleParking()" class="icon-state">
                        <icon-radio :active="form.parking == 1" />
                      </a>
                    </apartment-input>
                  </apartment-row>
                  <apartment-row class="pb-3x" v-else-if="data.has_reply && data.parking">
                    <apartment-label :cls="'span-3'">Ich habe Interesse an einem Abstellplatz in der Tiefgarage Eichbühlstrasse (Warteliste)</apartment-label>
                    <apartment-input :cls="'span-1 flex justify-center'">
                      <a href="" @click.prevent="toggleParking()" class="icon-state">
                        <icon-radio :active="true" />
                      </a>
                    </apartment-input>
                  </apartment-row>

                  <div class="mt-12x" v-if="form.accepted != null">
                    <a 
                      href="javascript:;" 
                      class="btn-primary is-small mb-3x"
                      @click="reply()">
                      <span>Antworten</span>
                    </a>
                    <a 
                      href="javascript:;" 
                      class="btn-secondary is-outline is-small"
                      @click="reset()">
                      <span>Abbrechen</span>
                    </a>
                  </div>
                  <div class="collection-text mt-16x">
                    <p>Haben Sie Fragen?</p>
                    <p>Camilla Walker steht Ihnen für weitere Informationen gerne zur Verfügung:<br>043 222 60 03<br><a href="mailto:wohnung@aporta-stiftung.ch">wohnung@aporta-stiftung.ch</a></p>
                  </div>
                </form>
              </div>
            </apartment-row>
            <apartment-row class="grid-cols-none mt-15x">
              <div>
                <h3>Beispielbilder</h3>
                <div class="grid-cols-12 grid-row-gap">
                  <figure class="span-12">
                    <img src="/assets/img/aporta-eglistrasse-wohnraum.jpg" class="is-responsive" width="1016" height="718">
                  </figure>
                  <figure class="span-6">
                    <img src="/assets/img/aporta-eglistrasse-nasszellen.jpg" class="is-responsive" width="1000" height="1415">
                  </figure>
                  <figure class="span-6">
                    <img src="/assets/img/aporta-eglistrasse-treppenhaus.jpg" class="is-responsive" width="1000" height="1415">
                  </figure>
                  <figure class="span-12">
                    <img src="/assets/img/aporta-eglistrasse-gartenblick.jpg" class="is-responsive" width="1016" height="718">
                  </figure>
                </div>
            </div>
            </apartment-row>
          </div>
        </apartment-grid>
      </apartment-wrapper>
    </div>
    <div v-else class="flex justify-center mt-15x">
      <p class="text-md"><strong>Dieses Angebot ist leider nicht mehr verfügbar.</strong></p>     
    </div>
  </site-main>

  <dialog-wrapper ref="dialogSubmitConfirm">
    <template #message>
      <div>
        <strong>Vielen Dank für Ihre Antwort.</strong>
      </div>
    </template>
    <template #button>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="dialogSubmitConfirm.hide()">Schliessen</a>
    </template>
  </dialog-wrapper>

</div>
</template>
<script setup>
import { ref, inject, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import NProgress from 'nprogress';
import http from '@/lib/http';
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import SiteHeader from '@/views/frontend/layout/Header.vue';
import SiteMain from '@/views/frontend/layout/Main.vue';
import PageMenu from '@/views/frontend/components/ui/Menu.vue';
import ApartmentWrapper from '@/components/ui/apartment/Wrapper.vue';
import ApartmentGrid from '@/components/ui/apartment/Grid.vue';
import ApartmentRow from '@/components/ui/apartment/Row.vue';
import ApartmentLabel from '@/components/ui/apartment/Label.vue';
import ApartmentInput from '@/components/ui/apartment/Input.vue';
import Isometrie from '@/components/ui/misc/Isometrie.vue';
import IconRadio from '@/components/ui/icons/Radio.vue';

const route = useRoute();
const estateKey = inject('estateKey');

const data = ref({});
const pagination = ref({});
const form = ref({
  accepted: null,
  parking: 0,
  comment: null,
});
const isFetched = ref(false);
const isValid = ref(false);
const hasValidationErrors = ref(false);
const dialogSubmitConfirm = ref(null);

const routes = {
  show: '/api/user-collection',
  reply: '/api/user-collection'
};

onMounted(() => fetch());

function fetch() {
  NProgress.start();
  isFetched.value = false;
  http.get(`${routes.show}/${route.params.uuid}/item/${route.params.itemUuid}`).then(response => {
    data.value = response.data.item;
    pagination.value = response.data.pagination;
    isFetched.value = true;
    isValid.value = response.data.valid;
    NProgress.done();
  });
}

function reply() {
  if (form.value.accepted == 0 && !form.value.comment) {
    hasValidationErrors.value = true;
    return false;
  }

  const payload = {
    'uuid': route.params.itemUuid,
    'accepted': form.value.accepted,
    'parking': form.value.parking,
    'comment': form.value.comment,
  };
  NProgress.start();
  http.post(routes.reply, payload).then(() => {
    reset();
    data.value.has_reply = true;
    data.value.parking = payload.parking;
    data.value.accepted = payload.accepted;
    data.value.comment = payload.comment;
    NProgress.done();
    dialogSubmitConfirm.value.show();
  });
}

function toggleAccept(value) {
  form.value.accepted = value;
  if (form.value.accepted !== 1) {
    form.value.parking = 0;
  }
}

function toggleParking() {
  form.value.parking = form.value.parking ? 0 : 1;
}

function reset() {
  form.value.accepted = null;
  form.value.parking = 0;
}

function removeValidationError() {
  hasValidationErrors.value = false;
}
</script>
