require('@/bootstrap');

// Vue
import Vue from 'vue';
window.Vue = Vue;

// Axios, Vue-Axios
import VueAxios from 'vue-axios';
import axios from 'axios';
window.axios = require('axios');
Vue.use(VueAxios, axios);

// Axios Interceptors
require('vue-axios-interceptors');

// Filters
require('@/mixins/Filters');

// Vue-Axios defaults
Vue.axios.defaults.withCredentials = true;

// Vue-Notifications
import Notifications from 'vue-notification';
Vue.use(Notifications);

// Store
import store from '@/config/store';

// Vue-Router
import VueRouter from 'vue-router';
Vue.use(VueRouter);

// Routes
import baseRoutes from '@/config/routes';
import apartmentRoutes from '@/views/backend/pages/apartment/config/routes';
import collectionRoutes from '@/views/backend/pages/collection/config/routes';
import tenantRoutes from '@/views/backend/pages/tenant/config/routes';
import userRoutes from '@/views/backend/pages/user/config/routes';

const router = new VueRouter(
  { 
    mode: 'history', 
    routes: [
      ...baseRoutes,
      ...apartmentRoutes,
      ...collectionRoutes,
      ...tenantRoutes,
      ...userRoutes,
    ]
  }
);

// App component
import AppComponent from '@/App.vue';

// Mount App
if (document.getElementById("app")) {
  const app = new Vue({
    mixins: [],
    components: { 
      AppComponent
    },
    router,
    store
  }).$mount('#app');
}