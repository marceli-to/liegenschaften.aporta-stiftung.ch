export default {

  data() {
    return {
      hasFilter: false,
      hasFilterResult: false,
    };
  },

  methods: {
    toggleFilter() {
      this.hasFilter = this.hasFilter ? false : true;
      this.hideSelector();
    },

    showFilter() {
      this.hasFilter = true;
      this.hideSelector();
    },

    hideFilter() {
      this.hasFilter = false;
    },

    resetFilter() {
      let filter = {
        set: false,
        state: null,
        amount: null,
        items: [],
        menu: {
          index: 1,
          current: null,
          prev: null,
          next: null
        }
      };
      this.$store.commit('filter', filter);
      this.hideFilter();
      this.fetch(this.$route.params.type);
    },

    setFilterItem(type, value) {
      let filter = this.$store.state.filter;
      filter[type] = value;
      filter['set'] = true;
      this.$store.commit('filter', filter);
      this.fetchFiltered();
    },

    setFilterMenu(data) {
      let items = [];
      this.data.forEach(function(item){
        items.push(item.uuid);
      });
      let filter = this.$store.state.filter;
      filter.items = items;
      filter.menu.index = 1; 

      if (filter.items.length == 1) {
        filter.menu.current = filter.items[0];
        filter.menu.prev = filter.items[0];
        filter.menu.next = filter.items[0];
      }
      else {
        filter.menu.current = items[0];
        filter.menu.prev = items[items.length-1];
        filter.menu.next = items[1];
      }
      this.$store.commit('filter', filter);
    },

    updateFilterMenu(uuid) {
      let filter = this.$store.state.filter;
      const index = filter.items.findIndex(x => x === uuid);

      filter.menu.current = filter.items[index];

      if (index == 0) {
        filter.menu.index = 1;
        filter.menu.prev = filter.items[filter.items.length - 1];
        filter.menu.next = filter.items.length == 1 ? filter.items[index] : filter.items[index+1];
      }
      else if (index == filter.items.length - 1) {
        filter.menu.index = filter.items.length;
        filter.menu.prev = filter.items[index-1];
        filter.menu.next = filter.items[0];
      }
      else {
        filter.menu.index = index + 1;
        filter.menu.prev = filter.items[index-1];
        filter.menu.next = filter.items[index+1];
      }
      this.$store.commit('filter', filter);
    },
  },
  watch: {
    '$route'() {
      this.hasFilter = false;
      this.updateFilterMenu(this.$route.params.uuid);
    }
  }
}
