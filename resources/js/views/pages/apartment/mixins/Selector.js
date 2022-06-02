export default {

  data() {
    return {
      hasSelector: false,
      hasSelectorResult: false,
      cachebuster: this.randomString(),
    };
  },

  methods: {
    toggleSelector() {
      this.hasSelector = this.hasSelector ? false : true;
      this.cachebuster = this.randomString(12);
      this.hideFilter();
    },

    showSelector() {
      this.hasSelector = true;
      this.hideFilter();
    },

    hideSelector() {
      this.hasSelector = false;
    },

    resetSelector() {
      let selector = {
        set: false,
        type: null,
      };
      this.$store.commit('selector', selector);
      this.hideSelector();
      this.fetch(this.$route.params.type);
    },

    setSelectorItem(type, value) {
      let selector = this.$store.state.selector;
      selector[type] = value;
      selector['set'] = true;
      this.$store.commit('selector', selector);
      //this.fetchSelected();
    },

    randomString(length) {
      var result = [];
      var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
      var charactersLength = characters.length;
      for (var i = 0; i < length; i++) {
        result.push(characters.charAt(Math.floor(Math.random() * charactersLength)));
      }
      return result.join('');
    }

  },
  watch: {
    '$route'() {
      this.hasSelector = false;
    }
  }
}
