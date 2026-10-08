import { ref, computed } from 'vue';
import { orderBy } from '@/lib/utils';

/**
 * Column sort of a list: sort('building.street') toggles the direction
 * when the same column is clicked again.
 */
export function useSort(data) {
  const sortBy = ref('');
  const sortDirection = ref('asc');

  function sort(key) {
    if (key === sortBy.value) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    }
    sortBy.value = key;
  }

  const sortedData = computed(() => orderBy(data.value, sortBy.value, sortDirection.value));

  return { sort, sortedData };
}
