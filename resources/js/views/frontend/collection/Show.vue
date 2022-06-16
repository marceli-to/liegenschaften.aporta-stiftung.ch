<template>
<div >
  <site-header :view="'show'"></site-header>
  <site-main v-if="isFetched">
    <page-menu :pagination="pagination"></page-menu>
    <apartment-wrapper>
      <h2 class="sm:hide">{{ data.room_description }}, {{ data.size }} m<sup>2</sup><br><em>{{ data.street }}, {{ data.city }}<br>{{ data.description }}</em></h2>
      <h2 class="xs:hide">{{ data.description }}&nbsp;&nbsp;<em>{{ data.rooms }} {{ data.size }} M<sup>2</sup></em></h2>
      <apartment-grid class="sm:grid-cols-10">
        <div class="span-6 sm:line-after">
          <apartment-row>
            <div class="span-4 is-first">
              <div class="xs:grid-cols-6">
                <h3 class="xs:span-3">Grundriss</h3>
                <div class="xs:span-3 sm:hide flex justify-end">
                  <a href="" target="_blank" class="icon" title="Download Grundriss als PDF">
                    <span>Download als PDF</span>
                  </a>
                </div>
              </div>
              <figure class="apartment-floorplan">
                <img :src="`/assets/media/${data.number}.svg`" height="600" width="600" class="is-responsive">
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
                    <div class="span-2"><label>Bezeichnung</label></div>
                    <div class="span-2">{{ data.description }}</div>
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
            <div class="span-4 is-first" v-if="!data.has_reply">
              <h3>An-/Abmeldung</h3>
              <form>
                <apartment-row class="pb-3x sm:pb-0">
                  <apartment-label :cls="'span-3'">Ich habe Interesse an der Wohnung</apartment-label>
                  <apartment-input :cls="'span-1 flex justify-center'">
                    <a href="" @click.prevent="toggleAccept(1)" class="icon-state">
                      <icon-radio :active="form.accept == 1" />
                    </a>
                  </apartment-input>
                </apartment-row>
                <apartment-row>
                  <apartment-label :cls="'span-3'">Ich habe kein Interesse an der Wohnung</apartment-label>
                  <apartment-input :cls="'span-1 flex justify-center'">
                    <a href="" @click.prevent="toggleAccept(0)" class="icon-state">
                      <icon-radio :active="form.accept == 0" />
                    </a>
                  </apartment-input>
                </apartment-row>
                <div v-if="form.accept == 0">
                  <div class="collection-text my-6x">
                    <p>Sie haben kein Interesse an der Wohnung? Bitte teilen Sie uns ihre Gründe mit.</p>
                  </div>
                  <textarea name="comment" class="is-outline" placeholder="Kommentar" v-model="form.comment"></textarea>
                </div>
                <div class="mt-12x" v-if="form.accept != null">
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
                  <p>Giancarlo Esempio steht Ihnen für weitere Informationen gerne zur Verfügung:<br>043 222 60 00<br><a href="mailo:esempio@test.ch">esempio@aporta-stiftung.ch</a></p>
                </div>
              </form>
            </div>
            <div class="span-4 is-first collection-text" v-else>
              <p>Sie haben dieses Angebot bereits beantwortet.</p>
            </div>
          </apartment-row>
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
        accept: null,
        comment: null,
      },

      // Routes
      routes: {
        show: '/api/user-collection',
        reply: '/api/user-collection'
      },

      // States
      isFetched: false,
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
        NProgress.done();
      });
    },

    reply() {
      let data = {
        'uuid': this.$route.params.itemUuid,
        'accepted': this.form.accept,
        'comment': this.form.comment,
      };
      NProgress.start();
      this.axios.post(`${this.routes.reply}`, data).then(response => {
        this.reset();
        this.data.has_reply = true;
        NProgress.done();
      });
    },

    toggleAccept(value) {
      this.form.accept = value;
    },

    reset() {
      this.form.accept = null;
    }
  },

  watch: {
    '$route'() {
      this.fetch();
    }
  }

};
</script>
