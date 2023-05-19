<template>
  <div>
    <site-header 
      :user="$store.state.user" 
      :view="'tenants'">
      <nav class="selector" v-if="hasSearch">
        <div>
          <div class="grid-cols-12">
            <div class="span-6">
              <h2>Mieter-Suche</h2>
              <input type="text" class="search" v-model="searchTerm" placeholder="Suche nach Vorname, Name, E-Mail, Telefon">
            </div>
          </div>
        </div>
        <a href="javascript:;" :class="[$store.state.filter.set ? 'is-active' : '', 'btn-primary is-filter']" @click.prevent="fetch()">Suchen</a>
        <a href="javascript:;" class="btn-secondary is-outline" @click.prevent="resetSearch()">Zurücksetzen</a>
      </nav>
    </site-header>
    <site-main v-if="isFetched">
      <div class="my-6x pr-6x w-full align-right">
        <a :href="`/export/mieter?v=${randomString()}`" target="_blank" class="link-export">
          Export Excel
          <icon-document class="ml-1x" />
        </a>
      </div>
      <list class="mt-6x" v-if="sortedData.length">
        <list-header>
          <list-item :class="'span-2 list-item-header line-after'">
            Vorname, Name
            <a href="" @click.prevent="sort('name')">
              <icon-sort />
            </a>
          </list-item>
          <list-item :class="'span-2 list-item-header line-after'">
            E-Mail
            <a href="" @click.prevent="sort('email')">
              <icon-sort />
            </a>
          </list-item>
          <list-item :class="'span-2 list-item-header line-after'">
            Telefon
          </list-item>
          <list-item :class="'span-3 list-item-header'">
            Addresse
            <a href="" @click.prevent="sort('apartment.building.street')">
              <icon-sort />
            </a>
          </list-item>
          <list-item :class="'span-3 list-item-header'">
            Wohnung
            <a href="" @click.prevent="sort('apartment.number')">
              <icon-sort />
            </a>
          </list-item>
        </list-header>
        <div 
          v-for="(d, index) in sortedData" 
          class="list-row no-hover" 
          :data-uuid="d.uuid"
          :key="d.id">
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <span>{{ d.firstname }} {{ d.name }}</span>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <span>{{ d.email }}</span>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-2 list-item line-after']">
            <span>{{ d.phone }}</span>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-3 list-item line-after']">
            <span>{{ d.apartment.building.street }}</span>
          </list-item>
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-3 list-item']">
            <span>{{ d.apartment.number }} / {{ d.apartment.description }}</span>
          </list-item>
        </div>
      </list>
      <list-empty class="mt-6x text-md" v-else>
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
  import Search from "@/views/backend/pages/mixins/TenantSearch";
  import IconSort from "@/components/ui/icons/Sort.vue";
  import IconState from "@/components/ui/icons/State.vue";
  import IconPlus from "@/components/ui/icons/Plus.vue";
  import IconCross from "@/components/ui/icons/Cross.vue";
  import IconCheckmark from '@/components/ui/icons/Checkmark.vue';
  import IconHourglass from "@/components/ui/icons/Hourglass.vue";
  import IconTrash from "@/components/ui/icons/Trash.vue";
  import IconLinkExternal from "@/components/ui/icons/LinkExternal.vue";
  import IconPencil from "@/components/ui/icons/Pencil.vue";
  import IconDocument from "@/components/ui/icons/Document.vue";
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
      IconSort,
      IconState,
      IconPlus,
      IconCross,
      IconCheckmark,
      IconHourglass,
      IconTrash,
      IconLinkExternal,
      IconPencil,
      IconDocument,
      List,
      ListRow,
      ListHeader,
      ListItem,
      ListAction,
      ListEmpty,
    },
   
    mixins: [ErrorHandling, Helpers, Sort, Search],
  
    data() {
      return {
  
        // Data
        data: [],

        // Search term
        searchTerm: '',
  
        // Routes
        routes: {
          get: '/api/tenants',
        },
  
  
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

      document.addEventListener('keydown', (e) => {
        if (e.keyCode === 13 && this.hasSearch && this.searchTerm !== '') {
          this.fetch();
        }
      });
    },
  
    methods: {
  
      fetch() {
        NProgress.start();
        this.isFetched = false;
        this.axios.get(`${this.routes.get}/${this.searchTerm}`).then(response => {
          this.data = response.data.data;
          this.isFetched = true;
          this.hideSearch();
          NProgress.done();
        });
      },
    },
  }
  </script>