import { store, emptyFilter } from '@/store';

/**
 * The apartment filter (header panel) and the prev/next menu it feeds for
 * the detail view. fetch / fetchFiltered: the list page's loaders.
 */
export function useFilter({ fetch, fetchFiltered } = {}) {

  function hideFilter() {
    store.ui.hasFilter = false;
  }

  function resetFilter() {
    store.filter = emptyFilter();
    hideFilter();
    fetch();
  }

  function isFilterAttribute(type, value) {
    return store.filter[type].includes(value);
  }

  function setFilterItem(type, value) {
    const filter = store.filter;

    if (filter[type] != null) {
      // Multi types
      if (Array.isArray(filter[type])) {
        const index = filter[type].indexOf(value);
        if (index > -1) {
          filter[type].splice(index, 1);
        }
        else {
          filter[type].push(value);
        }
      }
      // Single types
      else {
        filter[type] = filter[type] == value ? null : value;
      }
    }
    filter.set = true;
    fetchFiltered();
  }

  function setFilterMenu(data) {
    const items = data.map(item => item.uuid);
    const filter = store.filter;
    filter.items = items;
    filter.menu.index = 1;

    if (items.length == 1) {
      filter.menu.current = items[0];
      filter.menu.prev = items[0];
      filter.menu.next = items[0];
    }
    else {
      filter.menu.current = items[0];
      filter.menu.prev = items[items.length - 1];
      filter.menu.next = items[1];
    }
  }

  function updateFilterMenu(uuid) {
    const filter = store.filter;
    const index = filter.items.indexOf(uuid);

    filter.menu.current = filter.items[index];

    if (index == 0) {
      filter.menu.index = 1;
      filter.menu.prev = filter.items[filter.items.length - 1];
      filter.menu.next = filter.items.length == 1 ? filter.items[index] : filter.items[index + 1];
    }
    else if (index == filter.items.length - 1) {
      filter.menu.index = filter.items.length;
      filter.menu.prev = filter.items[index - 1];
      filter.menu.next = filter.items[0];
    }
    else {
      filter.menu.index = index + 1;
      filter.menu.prev = filter.items[index - 1];
      filter.menu.next = filter.items[index + 1];
    }
  }

  return { hideFilter, resetFilter, isFilterAttribute, setFilterItem, setFilterMenu, updateFilterMenu };
}
