import { store } from '@/store';

/**
 * The apartments picked for an offer (header counter, «Merken»)
 */
export function useCollection() {

  function resetCollection() {
    store.collection = { set: false, items: [] };
  }

  function addToCollection(uuid) {
    if (!store.collection.items.includes(uuid)) {
      store.collection.items.push(uuid);
      store.collection.set = true;
    }
  }

  function removeFromCollection(uuid) {
    const index = store.collection.items.indexOf(uuid);
    if (index > -1) {
      store.collection.items.splice(index, 1);
    }
    store.collection.set = store.collection.items.length > 0;
  }

  function isInCollection(uuid) {
    return store.collection.items.includes(uuid);
  }

  return { resetCollection, addToCollection, removeFromCollection, isInCollection };
}
