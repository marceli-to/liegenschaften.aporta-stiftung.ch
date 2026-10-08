<template>
<div>
  <site-header :view="'users'"></site-header>
  <site-main v-if="isFetched">
    <nav class="page-menu page-menu__users">
      <ul>
        <li class="start-3">
          <a href="/logout" @click.prevent="logout()">
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
        <list-item :class="'span-2 start-2 list-item-header line-after'">Vorname</list-item>
        <list-item :class="'span-2 list-item-header line-after'">Nachname</list-item>
        <list-item :class="'span-2 list-item-header line-after'">E-Mail</list-item>
        <list-item :class="'span-2 list-item-header line-after'">Passwort</list-item>
        <list-item :class="'span-2 list-item-header'">Admin</list-item>
      </list-header>
      <list-row class="no-hover is-narrow mb-5x">
        <list-item class="span-2 start-2 list-item is-first line-after">
          <input type="text" v-model="user.firstname" required @blur="validate($event, user)" :class="[errors.firstname ? 'is-invalid' : '', '']">
        </list-item>
        <list-item class="span-2 list-item is-first line-after">
          <input type="text" v-model="user.name" required @blur="validate($event, user)" :class="[errors.name ? 'is-invalid' : '', '']">
        </list-item>
        <list-item class="span-2 list-item is-first line-after">
          <input type="email" v-model="user.email" required @blur="validate($event, user)" :class="[errors.email ? 'is-invalid' : '', '']">
        </list-item>
        <list-item class="span-2 list-item is-first line-after">
          <input type="password" v-model="user.password" required @blur="validate($event, user)" :class="[errors.password ? 'is-invalid' : '', '']">
        </list-item>
        <list-item class="span-2 list-item is-first">
          <div class="mt-1x">
            <a href="" @click.prevent="toggleRole()">
              <icon-radio :active="user.role == 'admin' ? true : false" class="icon" />
            </a> 
          </div>
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
            <icon-radio :active="d.role == 'admin' ? true : false" class="icon" />
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
        <div class="mt-2x" v-for="error in validationErrors" :key="error">
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
        <strong>Bitte löschen von «{{ tempUser?.firstname }} {{ tempUser?.name }}» bestätigen!</strong>
      </div>
    </template>
    <template #actions>
      <a href="javascript:;" class="btn-primary mb-3x" @click.prevent="destroy()">löschen</a>
    </template>
  </dialog-wrapper>

</div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import NProgress from 'nprogress';
import http, { logout } from '@/lib/http';
import { validateRequired, validateEmail } from '@/lib/utils';
import { useSort } from '@/composables/useSort';
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import IconSort from "@/components/ui/icons/Sort.vue";
import IconPlus from "@/components/ui/icons/Plus.vue";
import IconCross from "@/components/ui/icons/Cross.vue";
import IconTrash from "@/components/ui/icons/Trash.vue";
import IconRadio from "@/components/ui/icons/Radio.vue";
import IconArrowRight from "@/components/ui/icons/ArrowRight.vue";
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListRow from "@/components/ui/layout/ListRow.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";
import ListEmpty from "@/components/ui/layout/ListEmpty.vue";

function emptyUser() {
  return {
    firstname: null,
    name: null,
    email: null,
    password: null,
    role: 'editor'
  };
}

const data = ref([]);
const user = ref(emptyUser());
const errors = ref({});
const tempUser = ref(null);
const validationErrors = ref([]);

const isFetched = ref(false);
const isValid = ref(false);
const isUpdate = ref(false);
const hasForm = ref(false);

const dialogValidationErrors = ref(null);
const dialogDeleteConfirm = ref(null);

const routes = {
  get: '/api/users',
  post: '/api/user',
  put: '/api/user',
  delete: '/api/user',
};

const messages = {
  emptyData: 'Sorry, es sind keine Datensätze vorhanden.',
};

const { sort, sortedData } = useSort(data);

onMounted(() => get());

function get() {
  NProgress.start();
  isFetched.value = false;
  http.get(routes.get).then(response => {
    data.value = response.data.data;
    isFetched.value = true;
    NProgress.done();
  });
}

function submit() {
  if (isValid.value) {
    isUpdate.value ? update() : create();
  }
}

function create() {
  NProgress.start();
  isFetched.value = false;
  http.post(routes.post, user.value, { handleErrors: false }).then(response => {
    data.value.push(response.data);
    toggleForm();
    resetForm();
    NProgress.done();
    isFetched.value = true;
  })
  .catch(error => {
    isFetched.value = true;
    handleValidationErrors(error);
  });
}

// The form edits the list's row itself, so the list shows the changes already
function update() {
  NProgress.start();
  isFetched.value = false;
  http.put(`${routes.put}/${user.value.id}`, user.value, { handleErrors: false }).then(() => {
    hideForm();
    NProgress.done();
    isFetched.value = true;
    isUpdate.value = false;
  })
  .catch(error => {
    isFetched.value = true;
    handleValidationErrors(error);
  });
}

function edit(d) {
  user.value = d;
  isUpdate.value = true;
  isValid.value = true;
  showForm();
}

function toggleRole() {
  user.value.role = user.value.role == 'admin' ? 'editor' : 'admin';
}

function destroy() {
  NProgress.start();
  isFetched.value = false;
  http.delete(`${routes.delete}/${tempUser.value.id}`).then(() => {
    data.value.splice(data.value.indexOf(tempUser.value), 1);
    tempUser.value = null;
    dialogDeleteConfirm.value.hide();
    NProgress.done();
    isFetched.value = true;
  });
}

function resetForm() {
  user.value = emptyUser();
  tempUser.value = null;
  isValid.value = false;
}

function toggleForm() {
  if (hasForm.value) resetForm();
  hasForm.value = !hasForm.value;
}

function hideForm() {
  hasForm.value = false;
  resetForm();
}

function showForm() {
  hasForm.value = true;
}

function validate(event, user) {
  if (
    validateRequired(user.name) &&
    validateRequired(user.firstname) &&
    validateEmail(user.email) &&
    (validateRequired(user.password) || isUpdate.value)) {
    event.target.classList.remove('is-invalid');
    isValid.value = true;
    return true;
  }
  if (event.target.type == 'email' && validateEmail(event.target.value)) {
    event.target.classList.remove('is-invalid');
    return;
  }
  if ((event.target.type == 'text' || event.target.type == 'password') && validateRequired(event.target.value)) {
    event.target.classList.remove('is-invalid');
    return;
  }
  event.target.classList.add('is-invalid');
  isValid.value = false;
}

// 422: the first message per field in a dialog, the fields marked
function handleValidationErrors(error) {
  NProgress.done();
  if (error.response?.status !== 422) {
    return;
  }
  validationErrors.value = [];
  errors.value = {};
  for (const key in error.response.data.errors) {
    validationErrors.value.push(error.response.data.errors[key][0]);
    errors.value[key] = true;
  }
  dialogValidationErrors.value.show();
}

function showConfirmDelete(d) {
  tempUser.value = d;
  dialogDeleteConfirm.value.show();
}

function hideValidationErrors() {
  dialogValidationErrors.value.hide();
}
</script>
