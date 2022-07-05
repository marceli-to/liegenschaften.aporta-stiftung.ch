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
                    <apartment-row v-if="data.shared_exterior">
                      <div class="span-2"><label>Aussenfläche</label></div>
                      <div class="span-2">gemeinsam an der Egligasse</div>
                    </apartment-row>
                  </div>
                  <div class="span-12 sm:span-6">
                    <isometrie :active="data.number" />
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
                  </apartment-row>
                  <apartment-row class="pb-3x" v-else-if="data.has_reply && data.accepted == 0">
                    <apartment-label :cls="'span-3'">Ich habe <strong>kein</strong> Interesse an diesem Angebot, bleibe aber für eine Wohnung in einer anderen Siedlung auf der Warteliste</apartment-label>
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
                <h3>Impressionen</h3>
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
import IconRadio from '@/components/ui/icons/Radio.vue';
import IconDocument from '@/components/ui/icons/Document.vue';

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
    IconCheckmark,
    IconRadio,
    IconDocument
  },

  mixins: [ErrorHandling],

  data() {
    return {

      // Data
      data: {},

      // Pagination
      pagination: {},

      // Form data
      form: {
        accepted: null,
        parking: 0,
        comment: null,
      },

      // Routes
      routes: {
        show: '/api/user-collection',
        reply: '/api/user-collection'
      },

      // States
      isFetched: false,
      isValid: false,
    };
  },

  mounted() {
    NProgress.configure({ showBar: false });
    this.fetch();
  },

  methods: {

    fetch() {
      NProgress.start();
      this.isFetched = false;
      this.axios.get(`${this.routes.show}/${this.$route.params.uuid}/item/${this.$route.params.itemUuid}`).then(response => {
        this.data = response.data.item;
        this.pagination = response.data.pagination;
        this.isFetched = true;
        this.isValid = response.data.valid;
        NProgress.done();
      });
    },

    reply() {
      let data = {
        'uuid': this.$route.params.itemUuid,
        'accepted': this.form.accepted,
        'parking': this.form.parking,
        'comment': this.form.comment,
      };
      NProgress.start();
      this.axios.post(`${this.routes.reply}`, data).then(response => {
        this.reset();
        this.data.has_reply = true;
        this.data.parking = data.parking;
        this.data.accepted = data.accepted;
        NProgress.done();
      });
    },

    toggleAccept(value) {
      this.form.accepted = value;
      if (this.form.accepted !== 1) {
        this.form.parking = 0;
      }
    },

    toggleParking() {
      this.form.parking = this.form.parking ? 0 : 1;
    },

    reset() {
      this.form.accepted = null;
      this.form.parking = 0;
    }
  },

  watch: {
    '$route'() {
      this.fetch();
    }
  }

};
</script>
