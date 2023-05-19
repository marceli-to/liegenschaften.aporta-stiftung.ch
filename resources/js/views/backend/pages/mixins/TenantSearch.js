export default {

  data() {
    return {
      hasSearch: false,
      hasSearchResult: false,
    };
  },

  methods: {
    toggleSearch() {
      this.hasSearch = this.hasSearch ? false : true;
    },

    showSearch() {
      this.hasSearch = true;
    },

    hideSearch() {
      this.hasSearch = false;
    },

    resetSearch() {
      this.hideSearch();
      this.searchTerm = '';
      this.fetch();
    },

  },

  watch: {
    '$route'() {
      this.hasSearch = false;
    }
  }
}
