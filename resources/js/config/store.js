import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    user: false,

    filter: {
      set: false,
      buildings: [],
      rooms: [],
      floors: [],
      states: [],
      collections: '',
      exterior: '',
      rent: '',
      items: [],
      menu: {
        index: 1,
        current: null,
        prev: null,
        next: null
      },
      referrer: null,
    },

    collection: {
      set: false,
      items: [],
    },
  },
  mutations: {
    user(state, user) {
      state.user = user;
    },
    filter(state, filter) {
      state.filter = filter;
    },
    collection(state, collection) {
      state.collection = collection;
    },
    referrer(state, referrer) {
      state.referrer = referrer;
    },
  }
});