export default {

  data() {
    return {
      hasCollection: false,
    };
  },

  methods: {

    resetCollection() {
      let collection = {
        set: false,
        items: [],
      };
      this.hasCollection = false;
      this.$store.commit('collection', collection);
    },

    addToCollection(uuid) {
      let collection = this.$store.state.collection;
      let index = collection.items.findIndex(item => item === uuid);
      if (index == -1) {
        collection.items.push(uuid);
        collection.set = true;
      }
      this.$store.commit('collection', collection);
    },

    removeFromCollection(uuid, refetch = false) {
      let collection = this.$store.state.collection;
      let index = collection.items.findIndex(item => item === uuid);
      if (index > -1) {
        collection.items.splice(index, 1);
      }
      collection.set = collection.items.length > 0 ? true : false;
      this.$store.commit('collection', collection);
      
      if (refetch) {
        this.fetch();
      }

    },

    isInCollection(uuid) {
      let collection = this.$store.state.collection;
      return collection.items.find(item => item === uuid) !== undefined ? true : false;
    }

  },
}
