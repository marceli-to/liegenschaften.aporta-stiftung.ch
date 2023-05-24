<template>
<div>
  <site-header :user="$store.state.user" :view="'users'"></site-header>
  <site-main v-if="isFetched">
    <nav class="page-menu page-menu__users">
      <ul>
        <li class="start-3">
          <a href="/logout">
            <icon-cross class="icon" :size="'md'" />
            <span>Abmelden</span>
          </a>
        </li>
        <li>
          <a href="" @click.prevent="update()" :class="[!isValid ? 'is-disabled' : '', '']">
            <icon-arrow-right :size="'md'" />
            <span>Speichern</span>
          </a>
        </li>
      </ul>
    </nav>

    <list class="mt-16x">
      <list-header class="is-narrow">
        <list-item :class="'span-2 start-3 list-item-header line-after'">Vorname</list-item>
        <list-item :class="'span-2 list-item-header line-after'">Nachname</list-item>
        <list-item :class="'span-2 list-item-header line-after'">E-Mail</list-item>
        <list-item :class="'span-2 list-item-header'">Passwort</list-item>
      </list-header>
      <list-row class="no-hover is-narrow mb-5x">
        <list-item class="span-2 start-3 list-item is-first line-after">
          <input type="text" v-model="user.firstname" required @blur="validate($event, user)" :class="[errors.firstname ? 'is-invalid' : '', '']">
        </list-item>
        <list-item class="span-2 list-item is-first line-after">
          <input type="text" v-model="user.name" required @blur="validate($event, user)" :class="[errors.name ? 'is-invalid' : '', '']">
        </list-item>
        <list-item class="span-2 list-item is-first line-after">
          <input type="email" v-model="user.email" required @blur="validate($event, user)" :class="[errors.email ? 'is-invalid' : '', '']">
        </list-item>
        <list-item class="span-2 list-item is-first">
          <input type="password" v-model="user.password">
        </list-item>
      </list-row>
    </list>

  </site-main>

  <dialog-wrapper ref="dialogValidationErrors">
    <template #message>
      <div>
        <strong>Es sind Fehler aufgetreten:</strong>
        <div class="mt-2x" v-for="error in validationErrors">
          {{error}}
        </div>
      </div>
    </template>
    <template #button>
      <a href="" @click.prevent="hideValidationErrors()" class="btn-primary mb-3x">
        Schliessen
      </a>
    </template>
  </dialog-wrapper>

  <dialog-wrapper ref="dialogSucess">
    <template #message>
      <div>
        <strong>Ihre Daten wurden aktualisiert.</strong>
      </div>
    </template>
    <template #button>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="$refs.dialogSucess.hide()">Schliessen</a>
    </template>
  </dialog-wrapper>

</div>
</template>
<script>
import NProgress from 'nprogress';
import ErrorHandling from '@/mixins/ErrorHandling';
import Helpers from "@/mixins/Helpers";
import Sort from "@/mixins/Sort";
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
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
import IconRadio from "@/components/ui/icons/Radio.vue";
import IconArrowRight from "@/components/ui/icons/ArrowRight.vue";
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
    IconRadio,
    IconArrowRight,
    DialogWrapper,
    List,
    ListRow,
    ListHeader,
    ListItem,
    ListAction,
    ListEmpty,
  },
  
  mixins: [ErrorHandling, Helpers, Sort],

  data() {
    return {

      // Data
      data: [],

      user: {
        firstname: null,
        name: null,
        email: null,
        password: null,
      },

      errors: {
        firstname: null,
        name: null,
        email: null,
        password: null,
      },

      tempUser: null,

      validationErrors: [],

      // Routes
      routes: {
        find: '/api/user',
        put: '/api/user',
      },

      // States
      isFetched: false,
      isValid: true,

      // Messages
      messages: {
        emptyData: 'Sorry, es sind keine Datensätze vorhanden.',
      },
    };
  },

  mounted() {
    NProgress.configure({ showBar: false });
    this.find();
  },

  methods: {

    find() {
      NProgress.start();
      this.isFetched = false;
      this.axios.get(`${this.routes.find}`).then(response => {
        this.user = response.data;
        console.log(this.user);
        this.isFetched = true;
        NProgress.done();
      });
    },

    update() {
      NProgress.start();
      this.isFetched = false;
      this.axios.put(`${this.routes.put}/${this.user.id}`, this.user).then(response => {
        NProgress.done();
        this.isFetched = true;
        this.$refs.dialogSucess.show();
      })
      .catch(error => {
        NProgress.done();
        this.isFetched = true;
        this.handleValidationErrors(error.response.data);
      });
    },

    validate(event, user) {

      if (
        this.validateRequired(user.name) && 
        this.validateRequired(user.firstname) && 
        this.validateEmail(user.email)
        ) {
        event.target.classList.remove('is-invalid');
        this.isValid = true;
        return true;
      }
      else {
        if (event.target.type == 'email' && this.validateEmail(event.target.value)) {
          event.target.classList.remove('is-invalid');
          return;
        }
        if (event.target.type == 'text' && this.validateRequired(event.target.value)) {
          event.target.classList.remove('is-invalid');
          return;
        }
        if (event.target.type == 'password' && this.validateRequired(event.target.value)) {
          event.target.classList.remove('is-invalid');
          return;
        }
      }
      event.target.classList.add('is-invalid');
      this.isValid = false;
      return;
    },

    handleValidationErrors(data) {
      let errors = [];
      for (let key in data.errors) {
        this.validationErrors.push(
          data.errors[key][0]
        );
        this.errors[key] = true;
      }
      // scroll to top
      this.showValidationErrors();
    },

    showValidationErrors() {
      this.$refs.dialogValidationErrors.show();
    },

    hideValidationErrors() {
      this.$refs.dialogValidationErrors.hide();
    },
  },
}
</script>