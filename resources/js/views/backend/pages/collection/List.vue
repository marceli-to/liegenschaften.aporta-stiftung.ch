<template>
<div>
  <site-header :user="$store.state.user"></site-header>
  <site-main v-if="isFetched">
    <list class="mt-6x" v-if="sortedData.length">
      <list-header>
        <list-item :class="'span-1'">
          &nbsp;
        </list-item>
        <list-item :class="'span-2 list-item-header line-after'">
          Name / E-Mail
          <a href="" @click.prevent="sort('collection.name')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-3 list-item-header line-after'">
          Adresse / Lage 
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Gesendet
          <a href="" @click.prevent="sort('sent_at')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Gelesen
          <a href="" @click.prevent="sort('read_at')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Beantwortet
          <a href="" @click.prevent="sort('replied_at')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-2 list-item-header'">
          Antwort
        </list-item>
        <list-item :class="'span-1 list-item-header flex direction-column align-center'">
          <div>
            Interesse
            <a href="" @click.prevent="sort('accepted')">
              <icon-sort />
            </a>
          </div>
        </list-item>
      </list-header>
      <div 
        v-for="(d, index) in sortedData" 
        :class="[d.uuid == $route.params.uuid ? 'is-marked' : '', 'list-row']" 
        :data-uuid="d.uuid"
        :key="d.id">
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-actions']">
          <a :href="`/angebot/${d.collection.uuid}`" target="_blank" title="Angebot anzeigen">
           <icon-link-external class="icon-link-external mb-6x" />
          </a>
          <a href="" @click.prevent="showConfirm(d.uuid)">
            <icon-trash class="icon-trash" />
          </a>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 1 }}">
            {{ d.collection.firstname }} {{ d.collection.name }}<br>
            {{ d.collection.email }}
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-3 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 1 }}">{{ d.apartment.building.street }}, {{ d.apartment.estate.city }}</router-link>
          <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 1 }}">{{ d.apartment.description }}</router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 1 }}">{{ d.sent_at ? d.sent_at : '–' }}</router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 1 }}">{{ d.read_at ? d.read_at : '–' }}</router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 1 }}">{{ d.replied_at ? d.replied_at : '–' }}</router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item']">
          <router-link :to="{name: 'apartment-show', params: { uuid: d.apartment.uuid, single: 1 }}">
            <span v-if="d.replied_at != null && d.accepted == 0">Nicht mehr auf Wohnungssuche<br><br></span>
            <span v-if="d.replied_at != null && d.accepted == 1">Hat Interesse an diesem Angebot<br><br></span>
            <span v-if="d.replied_at != null && d.accepted == 2">Kein Interesse an diesem Angebot, möchte aber auf Warteliste bleiben<br><br></span>
            <span v-if="d.replied_at != null && d.parking">Hat Interessse an Abstellplatz</span>
          </router-link>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-state']">
          <icon-checkmark v-if="d.accepted == 1"/>
          <icon-cross :size="'lg'" v-else-if="d.replied_at != null && (d.accepted == 0 || d.accepted == 2)" />
          <icon-hourglass v-else-if="d.replied_at == null && d.accepted == 0"/>
        </list-item>
      </div>
    </list>
    <list-empty class="mt-6x text-md" v-else>
      {{messages.emptyData}}
    </list-empty>
  </site-main>

  <dialog-wrapper ref="dialogDestroyConfirm">
    <template #message>
      <div>
        <strong>Möchten Sie den ausgewählten Eintrag wirklich löschen?</strong>
      </div>
    </template>
    <template #actions>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="destroy()">Ja</a>
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
import IconPlus from "@/components/ui/icons/Plus.vue";
import IconCross from "@/components/ui/icons/Cross.vue";
import IconCheckmark from '@/components/ui/icons/Checkmark.vue';
import IconHourglass from "@/components/ui/icons/Hourglass.vue";
import IconTrash from "@/components/ui/icons/Trash.vue";
import IconLinkExternal from "@/components/ui/icons/LinkExternal.vue";
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
    IconPlus,
    IconCross,
    IconCheckmark,
    IconHourglass,
    IconTrash,
    IconLinkExternal,
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

      // Routes
      routes: {
        get: '/api/collection-items',
        delete: '/api/collection-item',
      },

      itemToDelete: null,

      // States
      isFetched: false,

      // Messages
      messages: {
        emptyData: 'Es sind noch keine Angebote vorhanden.',
      },
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
      this.axios.get(`${this.routes.get}`).then(response => {
        this.data = response.data.data;
        this.isFetched = true;
        NProgress.done();
      });
    },

    destroy() {
      NProgress.start();
      this.axios.delete(`${this.routes.delete}/${this.itemToDelete}`).then(response => {
        this.fetch();
        this.itemToDelete = null;
        this.$refs.dialogDestroyConfirm.hide();
        NProgress.done();
      });
    },

    showConfirm(uuid) {
      this.itemToDelete = uuid;
      this.$refs.dialogDestroyConfirm.show();
    },

  },
}
</script>