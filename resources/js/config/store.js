import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    user: false,

    filter: {
      set: false,
      state_id: null,
      collections: null,
      building_id: null,
      room_id: null,
      exterior: null,
      items: [],
      menu: {
        index: 1,
        current: null,
        prev: null,
        next: null
      }
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
    }
  }
});