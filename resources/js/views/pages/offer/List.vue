<template>
<div>
  <site-header :user="$store.state.user"></site-header>
  <site-main v-if="isFetched">
    <nav :class="[!isActive ? 'is-disabled' : '', 'page-menu page-menu__offer']">
      <ul>
        <li class="start-5">
          <a href="">
            <icon-download />
            <span>Archivieren</span>
          </a>
        </li>
        <li>
          <a href="">
            <icon-trash :size="'lg'" />
            <span>Löschen</span>
          </a>
        </li>
      </ul>
    </nav>

    <list class="mt-18x" v-if="sortedData.length">
      <list-header>
        <list-item :class="'span-2 list-item-header line-after'">
          Name
          <a href="" @click.prevent="sort('building.street')">
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
          <a href="" @click.prevent="sort('room.abbreviation')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Gelesen
          <a href="" @click.prevent="sort('size')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header line-after'">
          Beantwortet
          <a href="" @click.prevent="sort('size_terrace')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-2 list-item-header'">
          Kommentar
          <a href="" @click.prevent="sort('size_patio')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header flex direction-column align-center'">
          <div>
            Status
            <a href="" @click.prevent="sort('state_id')">
              <icon-sort />
            </a>
          </div>
        </list-item>
      </list-header>
      <div 
        v-for="(d, index) in sortedData" 
        class="list-row" 
        :key="d.id">
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <span>{{ d.collection.firstname }} {{ d.collection.name }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <span>{{ d.collection.email }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
          <span>{{ d.apartment.building.street }}, {{ d.apartment.building.city }}</span>
          <span>{{ d.apartment.description }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <span>{{ d.collection.sent_at }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <span>{{ d.collection.read_at }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item line-after']">
          <span>{{ d.collection.replied_at }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item']">
          <span>{{ d.collection.comment }}</span>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-state']">
          <!-- <icon-state :id="collection.state_id" /> -->
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
import ErrorHandling from "@/mixins/ErrorHandling";
import Helpers from "@/mixins/Helpers";
import Sort from "@/mixins/Sort";
import Filter from "@/views/pages/apartment/mixins/Filter";
import Collection from "@/views/pages/apartment/mixins/Collection";
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import IconSort from "@/components/ui/icons/Sort.vue";
import IconState from "@/components/ui/icons/State.vue";
import IconPlus from "@/components/ui/icons/Plus.vue";
import IconTrash from "@/components/ui/icons/Trash.vue";
import IconCross from "@/components/ui/icons/Cross.vue";
import IconDownload from "@/components/ui/icons/Download.vue";
import SiteHeader from '@/views/layout/Header.vue';
import SiteMain from '@/views/layout/Main.vue';
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
        get: '/api/collections',
        getItems: '/api/collection/items',
      },

      // States
      isFetched: false,
      isActive: false,

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
      this.axios.get(`${this.routes.getItems}`).then(response => {
        this.data = response.data.data;
        this.isFetched = true;
        NProgress.done();
      });
    },
  },

}
</script>