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
import collectionRoutes from '@/views/frontend/collection/config/routes';


const router = new VueRouter(
  { 
    mode: 'history', 
    routes: [
      ...collectionRoutes,
    ]
  }
);

// App component
import CollectionComponent from '@/Collection.vue';

// Mount App
if (document.getElementById("collection")) {
  const app = new Vue({
    mixins: [],
    components: { 
      CollectionComponent
    },
    router,
    store
  }).$mount('#collection');
}