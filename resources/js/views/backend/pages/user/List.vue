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
          <a href="" @click.prevent="submit()" :class="[!isValid ? 'is-disabled' : '', '']">
            <icon-arrow-right :size="'md'" />
            <span>Speichern</span>
          </a>
        </li>
      </ul>
      <div class="flex justify-center mt-6x">
        <a href="" @click.prevent="toggleForm()">
          <icon-plus class="icon" />
        </a>
      </div>
    </nav>

    <list class="mt-8x" v-if="hasForm">
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
          <input type="password" v-model="user.password" required @blur="validate($event, user)" :class="[errors.password ? 'is-invalid' : '', '']">
        </list-item>
      </list-row>
    </list>

    <list class="mt-12x" v-if="sortedData.length">
      <list-header>
        <list-item :class="'span-1 list-item-header flex justify-center'">
          Admin
        </list-item>
        <list-item :class="'span-3 list-item-header line-after'">
          Vorname
          <a href="" @click.prevent="sort('firstname')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-3 list-item-header line-after'">
          Name
          <a href="" @click.prevent="sort('name')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-4 list-item-header'">
          E-Mail
          <a href="" @click.prevent="sort('email')">
            <icon-sort />
          </a>
        </list-item>
        <list-item :class="'span-1 list-item-header flex direction-column align-center'">
          <div>Löschen</div>
        </list-item>
      </list-header>
      <div 
        v-for="(d, index) in sortedData" 
        class="list-row" 
        :data-uuid="d.uuid"
        :key="d.id">
          <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-action']">
          <a href="" @click.prevent="edit(d)">
            <icon-radio :active="'true'" class="icon" />
          </a> 
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-3 list-item line-after']">
          <a href="" @click.prevent="edit(d)">
            <span>{{ d.firstname }}</span>
          </a>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-3 list-item line-after']">
          <a href="" @click.prevent="edit(d)">
            <span>{{ d.name }}</span>
          </a>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-4 list-item line-after']">
          <a href="" @click.prevent="edit(d)">
            <span>{{ d.email }}</span>
          </a>
        </list-item>
        <list-item :class="[index == 0 ? 'is-first' : '', 'span-1 list-item-state']">
          <a href="" @click.prevent="showConfirmDelete(d)">
            <icon-trash class="icon-trash" />
          </a>
      </list-item>
      </div>
    </list>
    <list-empty class="mt-6x text-md" v-else>
      {{messages.emptyData}}
    </list-empty>
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

  <dialog-wrapper ref="dialogDeleteConfirm">
    <template #message>
      <div>
        <strong>Bitte löschen von «{{ tempUser.firstname }} {{ tempUser.name }}» bestätigen!</strong>
      </div>
    </template>
    <template #actions>
      <a href="javascript:;" class="btn-primary mb-3x" @click.prevent="destroy()">löschen</a>
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
        get: '/api/users',
        post: '/api/user',
        put: '/api/user',
        delete: '/api/user',
      },

      // States
      isFetched: false,
      isValid: false,
      isUpdate: false,
      hasForm: false,

      // Messages
      messages: {
        emptyData: 'Sorry, es sind keine Datensätze vorhanden.',
      },
    };
  },

  mounted() {
    NProgress.configure({ showBar: false });
    this.get();
  },

  methods: {

    get() {
      NProgress.start();
      this.isFetched = false;
      this.axios.get(`${this.routes.get}`).then(response => {
        this.data = response.data.data;
        this.isFetched = true;
        NProgress.done();
      });
    },

    submit() {
      if (this.isValid) {
        this.isUpdate ? this.update() : this.create();
      }
    },

    create() {
      NProgress.start();
      this.isFetched = false;
      this.axios.post(this.routes.post, this.user).then(response => {
        this.data.push(response.data);
        this.toggleForm();
        this.resetForm();
        NProgress.done();
        this.isFetched = true;
      })
      .catch(error => {
        NProgress.done();
        this.isFetched = true;
        this.handleValidationErrors(error.response.data);
      });
    },

    update() {
      NProgress.start();
      this.isFetched = false;
      this.axios.put(`${this.routes.post}/${this.user.id}`, this.user).then(response => {
        this.data[this.data.indexOf(this.tempUser)] = response.data;
        this.hideForm();
        NProgress.done();
        this.isFetched = true;
        this.isUpdate = false;
      })
      .catch(error => {
        NProgress.done();
        this.data[this.data.indexOf(this.tempUser)] = this.tempUser;
        this.isFetched = true;
        this.handleValidationErrors(error.response.data);
      });
    },

    edit(user) {
      this.user = user;
      this.isUpdate = true;
      this.isValid = true;
      this.showForm();
    },

    destroy() {
      NProgress.start();
      this.isFetched = false;
      this.axios.delete(`${this.routes.delete}/${this.tempUser.id}`).then(response => {
        this.data.splice(this.data.indexOf(this.tempUser), 1);
        this.tempUser = null;
        this.$refs.dialogDeleteConfirm.hide();
        NProgress.done();
        this.isFetched = true;
      });
    },

    resetForm() {
      this.user = {
        firstname: null,
        name: null,
        email: null,
        password: null,
      };
      this.tempUser = null;
      this.isValid = false;
    },

    toggleForm() {
      if (this.hasForm) this.resetForm();
      this.hasForm = this.hasForm ? false : true;
    },

    hideForm() {
      this.hasForm = false;
      this.resetForm();
    },

    showForm() {
      this.hasForm = true;
    },

    validate(event, user) {

      if (
        this.validateRequired(user.name) && 
        this.validateRequired(user.firstname) && 
        this.validateEmail(user.email) &&
        (this.validateRequired(user.password) || this.isUpdate)) {
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

    showConfirmDelete(user) {
      this.tempUser = user;
      this.$refs.dialogDeleteConfirm.show();
    },

    hideValidationErrors() {
      this.$refs.dialogValidationErrors.hide();
    },

    hideConfirmDelete() {
      this.$refs.dialogDeleteConfirm.hide();
    },

  },
}
</script>