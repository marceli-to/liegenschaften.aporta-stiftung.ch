import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    user: false,
    filter: {
      set: false,
      state_id: null,
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
    selector: {
      set: false,
      type: null,
    },
  },
  mutations: {
    user(state, user) {
      state.user = user;
    },
    filter(state, filter) {
      state.filter = filter;
    },
    selector(state, selector) {
      state.selector = selector;
    }
  }
});