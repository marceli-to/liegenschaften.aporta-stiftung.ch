export default {

  data() {
    return {
      hasCollection: false,
    };
  },

  methods: {

    resetCollection() {
      let collection = {
        items: [],
      };
      this.$store.commit('collection', collection);
    },

    addToCollection(uuid) {
      let collection = this.$store.state.collection;
      let index = collection.items.findIndex(item => item === uuid);
      if (index == -1) {
        collection.items.push(uuid);
      }
      this.$store.commit('collection', collection);
    },

    removeFromCollection(uuid) {
      let collection = this.$store.state.collection;
      let index = collection.items.findIndex(item => item === uuid);
      if (index > -1) {
        collection.items.splice(index, 1);
      }
      this.$store.commit('collection', collection);
    },

    isInCollection(uuid) {
      let collection = this.$store.state.collection;
      return collection.items.find(item => item === uuid) !== undefined ? true : false;
    }

  },
}
