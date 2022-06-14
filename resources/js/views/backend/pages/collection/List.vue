<template>
<div>
  <site-header :user="$store.state.user"></site-header>
  <site-main v-if="isFetched">
    <nav :class="[marked.length == 0 ? 'is-disabled' : '', 'page-menu page-menu__offer']">
      <ul>
        <li class="start-5">
          <a href="" @click.prevent="archive()">
            <icon-download />
            <span>Archivieren {{ marked.length > 0 ? '(' + marked.length +')' : ''}}</span>
          </a>
        </li>
        <li>
          <a href="" @click.prevent="destroy()">
            <icon-trash :size="'lg'" />
            <span>Löschen {{ marked.length > 0 ? '(' + marked.length +')' : ''}}</span>
          </a>
        </li>
      </ul>
    </nav>

    <list class="mt-18x" v-if="sortedData.length">
      <list-header>
        <list-item :class="'span-2 list-item-header line-after'">
          Name
          <a href="" @click.prevent="sort('collection.name')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-2 list-item-header line-after'">
          E-Mail
        </list-item>
        <list-item :class="'span-2 list-item-header line-after'">
          Angebote Wohnung
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
          Kommentar
        </list-item>
        <list-item :class="'span-1 list-item-header flex direction-column align-center'">
          <div>
            Status
            <a href="" @click.prevent="sort('accepted')">
              <icon-sort />
            </a>
          </div>
        </list-item>
      </list-header>
      <div 
        v-for="(d, index) in sortedData" 
        :class="[isMarked(d.uuid) ? 'is-marked' : '', 'list-row']" 
        :key="d.id"
        @click="mark(d.uuid)">
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <span>{{ d.collection.firstname }} {{ d.collection.name }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <span>{{ d.collection.email }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <span>{{ d.apartment.building.street }}, {{ d.apartment.estate.city }}</span>
          <span>{{ d.apartment.description }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <span>{{ d.sent_at ? d.sent_at : '–' }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <span>{{ d.read_at ? d.read_at : '–' }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <span>{{ d.replied_at ? d.replied_at : '–' }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item']">
          <span>{{ d.comment }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-state']">
          <icon-checkmark v-if="d.accepted == 1"/>
          <icon-cross :size="'lg'" v-else-if="d.replied_at != null && d.accepted == 0" />
          <icon-hourglass v-else-if="d.replied_at == null && d.accepted == 0"/>
        </list-item>
      </div>
    </list>
    <list-empty v-else>
      {{messages.emptyData}}
    </list-empty>
  </site-main>
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
import IconTrash from "@/components/ui/icons/Trash.vue";
import IconCross from "@/components/ui/icons/Cross.vue";
import IconCheckmark from '@/components/ui/icons/Checkmark.vue';
import IconHourglass from "@/components/ui/icons/Hourglass.vue";
import IconDownload from "@/components/ui/icons/Download.vue";
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
    IconTrash,
    IconCross,
    IconDownload,
    IconCheckmark,
    IconHourglass,
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
        archive: '/api/collection-items/archive',
        delete: '/api/collection-items/delete',
      },

      marked: [],

      // States
      isFetched: false,

      // Messages
      messages: {
        emptyData: 'Es sind noch keine Daten vorhanden...',
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

    archive() {
      NProgress.start();
      this.isFetched = true;
      this.axios.put(`${this.routes.archive}`, {items: this.marked}).then(response => {
        this.marked = [];
        this.fetch();
        NProgress.done();
      });
    },

    destroy() {
      NProgress.start();
      this.isFetched = true;
      this.axios.put(`${this.routes.delete}`, {items: this.marked}).then(response => {
        this.marked = [];
        this.fetch();
        NProgress.done();
      });
    },

    mark(uuid) {
      let index = this.marked.findIndex(item => item === uuid);
      if (index > -1) {
        this.marked.splice(index, 1);
      }
      else if (index == -1) {
       this.marked.push(uuid);
      }
    },

    isMarked(uuid) {
      return this.marked.find(item => item === uuid) !== undefined ? true : false;
    }
  },
}
</script>