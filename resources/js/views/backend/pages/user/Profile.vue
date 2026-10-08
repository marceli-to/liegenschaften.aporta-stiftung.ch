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

  <dialog-wrapper ref="dialogSucess">
    <template #message>
      <div>
        <strong>Ihre Daten wurden aktualisiert.</strong>
      </div>
    </template>
    <template #button>
      <a href="javascript:;" class="btn-primary mb-3x" @click.stop="dialogSucess.hide()">Schliessen</a>
    </template>
  </dialog-wrapper>

</div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import NProgress from 'nprogress';
import http, { logout } from '@/lib/http';
import { validateRequired, validateEmail } from '@/lib/utils';
import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import IconCross from "@/components/ui/icons/Cross.vue";
import IconArrowRight from "@/components/ui/icons/ArrowRight.vue";
import SiteHeader from '@/views/backend/layout/Header.vue';
import SiteMain from '@/views/backend/layout/Main.vue';
import List from "@/components/ui/layout/List.vue";
import ListHeader from "@/components/ui/layout/ListHeader.vue";
import ListRow from "@/components/ui/layout/ListRow.vue";
import ListItem from "@/components/ui/layout/ListItem.vue";

const user = ref({
  firstname: null,
  name: null,
  email: null,
  password: null,
});
const errors = ref({});
const validationErrors = ref([]);

const isFetched = ref(false);
const isValid = ref(true);

const dialogValidationErrors = ref(null);
const dialogSucess = ref(null);

const routes = {
  find: '/api/user',
  put: '/api/user',
};

onMounted(() => find());

function find() {
  NProgress.start();
  isFetched.value = false;
  http.get(routes.find).then(response => {
    user.value = response.data;
    isFetched.value = true;
    NProgress.done();
  });
}

function update() {
  NProgress.start();
  isFetched.value = false;
  http.put(`${routes.put}/${user.value.id}`, user.value, { handleErrors: false }).then(() => {
    NProgress.done();
    isFetched.value = true;
    dialogSucess.value.show();
  })
  .catch(error => {
    isFetched.value = true;
    handleValidationErrors(error);
  });
}

function validate(event, user) {
  if (validateRequired(user.name) && validateRequired(user.firstname) && validateEmail(user.email)) {
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

function hideValidationErrors() {
  dialogValidationErrors.value.hide();
}
</script>
