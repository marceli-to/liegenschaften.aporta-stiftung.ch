import { reactive } from 'vue';

/**
 * Admin state, in place of Vuex: the user, the apartment filter (with the
 * prev/next menu of the detail view), the apartments picked for an offer,
 * and which panel the header has open on the current page.
 */
export function emptyFilter() {
  return {
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
  };
}

export const store = reactive({
  user: false,
  filter: emptyFilter(),
  collection: {
    set: false,
    items: [],
  },
  referrer: null,

  // Header panels (were the pages' hasFilter / hasSearch / hasCollection)
  ui: {
    hasFilter: false,
    hasSearch: false,
    hasCollection: false,
  },
});

/**
 * Close the header panels when leaving a page
 */
export function resetUi() {
  store.ui.hasFilter = false;
  store.ui.hasSearch = false;
  store.ui.hasCollection = false;
}
