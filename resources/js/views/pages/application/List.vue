<template>
<div>
  <site-header :user="$store.state.user">
    <nav class="selector" v-if="hasFilter && isFetchedStates">
      <div>
        <div class="grid-cols-12">
          <div class="span-4 start-2">
            <h2>Status</h2>
            <div class="grid-cols-2">
              <div v-for="state in dataStates" :key="state.id">
                <a href="javascript:;" @click.prevent="setFilterItem('state', state.id)">
                  <icon-radio-active v-if="$store.state.filter.state == state.id" />
                  <icon-radio v-else />
                  <span>{{state.description}}</span>
                </a>
              </div>
            </div>
          </div>
          <div class="span-4 start-7">
            <h2>Betrag</h2>
            <a href="" @click.prevent="setFilterItem('amount', 'lt:20000')">
              <icon-radio-active v-if="$store.state.filter.amount == 'lt:20000'" />
              <icon-radio v-else />
              <span>&lt; 20000</span>
            </a>
            <a href="" @click.prevent="setFilterItem('amount', 'gt:20000')">
              <icon-radio-active v-if="$store.state.filter.amount == 'gt:20000'" />
              <icon-radio v-else />
              <span>&gt; 20000</span>
            </a>
          </div>
        </div>
      </div>
      <a href="javascript:;" :class="[$store.state.filter.set ? 'is-active' : '', 'btn-primary is-filter']" @click.prevent="hideFilter()">Anzeigen</a>
      <a href="javascript:;" class="btn-secondary is-outline" @click.prevent="resetFilter()">Zurücksetzen</a>
    </nav>
    <nav class="selector" v-if="hasSelector">
      <div>
        <div class="grid-cols-12">
          <!--
          <div class="span-3 start-2">
            <h2>Zusagen/Absagen</h2>
            <div>
              <a href="javascript:;" @click.prevent="setSelectorItem('type', 'reply_all')">
                <icon-radio-active v-if="$store.state.selector.type == 'reply_all'" />
                <icon-radio v-else />
                <span>Alle Gesuche</span>
              </a>
              <a href="javascript:;" @click.prevent="setSelectorItem('type', 'reply_approved')">
                <icon-radio-active v-if="$store.state.selector.type == 'reply_approved'" />
                <icon-radio v-else />
                <span>Genehmigte</span>
              </a>
              <a href="javascript:;" @click.prevent="setSelectorItem('type', 'reply_denied')">
                <icon-radio-active v-if="$store.state.selector.type == 'reply_denied'" />
                <icon-radio v-else />
                <span>Abgelehnte</span>
              </a>
            </div>
          </div>
          <div class="span-3">
            <h2>Archivieren</h2>
            <div>
              <a href="javascript:;" @click.prevent="setSelectorItem('type', 'archive_all')">
                <icon-radio-active v-if="$store.state.selector.type == 'archive_all'" />
                <icon-radio v-else />
                <span>Alle Gesuche</span>
              </a>
              <a href="javascript:;" @click.prevent="setSelectorItem('type', 'archive_approved')">
                <icon-radio-active v-if="$store.state.selector.type == 'archive_approved'" />
                <icon-radio v-else />
                <span>Genehmigte</span>
              </a>
              <a href="javascript:;" @click.prevent="setSelectorItem('type', 'archive_denied')">
                <icon-radio-active v-if="$store.state.selector.type == 'archive_denied'" />
                <icon-radio v-else />
                <span>Abgelehnte</span>
              </a>
            </div>
          </div>
          -->
          <div class="span-3 start-2">
            <h2>Exportieren</h2>
            <div>
              <a href="javascript:;" @click.prevent="setSelectorItem('type', 'export_all')">
                <icon-radio-active v-if="$store.state.selector.type == 'export_all'" />
                <icon-radio v-else />
                <span>Alle Gesuche</span>
              </a>
              <a href="javascript:;" @click.prevent="setSelectorItem('type', 'export_approved')">
                <icon-radio-active v-if="$store.state.selector.type == 'export_approved'" />
                <icon-radio v-else />
                <span>Genehmigte</span>
              </a>
              <a href="javascript:;" @click.prevent="setSelectorItem('type', 'export_denied')">
                <icon-radio-active v-if="$store.state.selector.type == 'export_denied'" />
                <icon-radio v-else />
                <span>Abgelehnte</span>
              </a>
            </div>
          </div>
        </div>
      </div>
        <a 
          :href="'/export/?v=' + cachebuster" 
          target="_blank"
          class="btn-primary is-filter" 
          @click="hideSelector()"
          v-if="$store.state.selector.type && $store.state.selector.type.includes('export_')">
          Exportieren
        </a>
        <a
          href="javascript:;" 
          class="btn-primary is-filter" 
          @click.prevent="hideSelector()"
          v-else>
          Anzeigen
        </a>
        <a 
          href="javascript:;" 
          class="btn-secondary is-outline" 
          @click.prevent="resetSelector()">
          Abbrechen
        </a>
    </nav>
  </site-header>
  <site-main v-if="isFetched">
    <list v-if="data.length">
      <list-row-header>
        <list-item :cls="'span-1 list-item-header'">&nbsp;</list-item>
        <list-item :cls="'span-1 list-item-header line-after'">
          Eingang
          <a href="" @click.prevent="sort('created_at_timestamp')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :cls="'span-3 list-item-header line-after'">
          Organisation
          <a href="" @click.prevent="sort('name')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :cls="'span-1 list-item-header line-after'">
          Beantragt
          <a href="" @click.prevent="sort('requested_contribution')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :cls="'span-1 list-item-header line-after'">
          Bewilligt
          <a href="" @click.prevent="sort('project_contribution_approved')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :cls="'span-2 list-item-header line-after'">Kontakt</list-item>
        <list-item :cls="'span-2 list-item-header'">E-Mail</list-item>
        <list-item :cls="'span-1 list-item-header flex direction-column align-center'">
          <div>
            Status
            <a href="" @click.prevent="sort('application_state_id')">
              <icon-sort />
            </a>
          </div>
        </list-item>
      </list-row-header>
      <list-row v-for="d in sortedData" :key="d.uuid" :class="[isNew(d) ? 'is-new' : '']">
        <list-item :cls="'span-1 list-item-bullet'">
          <bullet v-if="isNew(d)" />
        </list-item>
        <list-item :cls="'span-1 list-item line-after'">
          <router-link :to="{name: 'application-show', params: { type: $route.params.type, uuid: d.uuid }}">
            {{ d.created_at }}
          </router-link>
        </list-item>
        <list-item :cls="'span-3 list-item line-after'">
          <router-link :to="{name: 'application-show', params: { type: $route.params.type, uuid: d.uuid }}">
            {{ d.name }}
          </router-link>
        </list-item>
        <list-item :cls="'span-1 list-item line-after'">
          <router-link :to="{name: 'application-show', params: { type: $route.params.type, uuid: d.uuid }}">
            {{ d.project_contribution_requested | currency }}
          </router-link>
        </list-item>
        <list-item :cls="'span-1 list-item line-after'">
          <router-link :to="{name: 'application-show', params: { type: $route.params.type, uuid: d.uuid }}">
            {{ d.project_contribution_approved | currency }}
          </router-link>
        </list-item>
        <list-item :cls="'span-2 list-item line-after'">
          <router-link :to="{name: 'application-show', params: { type: $route.params.type, uuid: d.uuid }}">
            {{ d.applicant_name }}
          </router-link>
        </list-item>
        <list-item :cls="'span-2 list-item line-after'">
          <router-link :to="{name: 'application-show', params: { type: $route.params.type, uuid: d.uuid }}">
            {{ d.applicant_email }}
          </router-link>
        </list-item>
        <list-item :cls="'span-1 list-item-state flex justify-center'">
          <router-link :to="{name: 'application-show', params: { type: $route.params.type, uuid: d.uuid }}" class="icon-state">
            <icon-state :id="d.application_state_id" />
          </router-link>
        </list-item>
      </list-row>
    </list>
    <list-empty v-else>
      {{messages.emptyData}}
    </list-empty>
  </site-main>
</div>
</template>
<script>
import NProgress from 'nprogress';
import ErrorHandling from "@/mixins/ErrorHandling";
import Helpers from "@/mixins/Helpers";
import Sort from "@/mixins/Sort";
import Filter from "@/views/pages/application/mixins/Filter";
import Selector from "@/views/pages/application/mixins/Selector";
import IconSort from "@/components/ui/icons/Sort.vue";
import IconState from "@/components/ui/icons/State.vue";
import IconRadio from "@/components/ui/icons/Radio.vue";
import IconRadioActive from "@/components/ui/icons/RadioActive.vue";
import Bullet from "@/components/ui/misc/Bullet.vue";
import SiteHeader from '@/views/layout/Header.vue';
import SiteMain from '@/views/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListRowHeader from "@/components/ui/layout/ListRowHeader.vue";
import ListRow from "@/components/ui/layout/ListRow.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import ListAction from "@/components/ui/layout/ListAction.vue";
import ListEmpty from "@/components/ui/layout/ListEmpty.vue";

export default {

  components: {
    NProgress,
    SiteHeader,
    SiteMain,
    Bullet,
    IconSort,
    IconState,
    IconRadio,
    IconRadioActive,
    List,
    ListRow,
    ListRowHeader,
    ListItem,
    ListAction,
    ListEmpty,
  },

  mixins: [ErrorHandling, Helpers, Sort, Filter, Selector],

  data() {
    return {

      // Data
      data: [],

      // Data states
      dataStates: [],

      // Routes
      routes: {
        list: '/api/applications',
        listFilter: '/api/applications/filter',
        listStates: '/api/application-states',
        toggle: '/api/application/state',
        destroy: '/api/application'
      },

      // States
      isFetched: false,
      isFetchedStates: false,

      // Messages
      messages: {
        emptyData: 'Es sind noch keine Gesuche vorhanden...',
        confirmDestroy: 'Bitte löschen bestätigen!',
        updated: 'Status geändert',
      },
    };
  },

  mounted() {
    NProgress.configure({ showBar: false });
    this.beforeFetch(this.$route.params.type)
    this.fetchStates();
  },

  methods: {

    beforeFetch(type) {
      if (type == 'aktuell' && this.$store.state.filter.set) {
        this.fetchFiltered(type);
        return;
      }
      this.fetch(type)
    },

    fetch(type) {
      this.isFetched = false;
      NProgress.start();
      this.axios.get(`${this.routes.list}/${type}`).then(response => {
        this.data = response.data.data;
        this.isFetched = true;
        NProgress.done();
      });
    },

    fetchStates() {
      this.isFetchedStates = false;
      NProgress.start();
      this.axios.get(`${this.routes.listStates}`).then(response => {
        this.dataStates = response.data.data;
        this.isFetchedStates = true;
        NProgress.done();
      });
    },

    fetchFiltered() {
      let param = {
        state: this.$store.state.filter.state ? this.$store.state.filter.state : null,
        amount: this.$store.state.filter.amount ? this.$store.state.filter.amount : null
      };
      NProgress.start();
      this.isFetched = false;
      this.axios.post(`${this.routes.listFilter}`, param).then(response => {
        this.data = response.data.data;
        this.setFilterMenu(this.data);
        this.isFetched = true;
        NProgress.done();
      });
    },

    isNew(application) {
      if (application.users) {
        if (application.users.length == 0) {
          return true;
        }
        const index = application.users.findIndex(x => x.user_uuid === this.$store.state.user.uuid);
        return index > -1 ? false : true;
      }
    },
  },
  watch: {
    '$route'() {
      this.beforeFetch(this.$route.params.type)
    }
  },
}
</script>