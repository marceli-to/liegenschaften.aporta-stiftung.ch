<template>
<div class="iso-wrapper" ref="root" v-html="markup"></div>
</template>
<script setup>
import { ref, watch } from 'vue';

// One or more SVGs per estate (resources/isometrie/{estate}/*.svg, e.g. one
// per building), each apartment a [data-id] group. Loaded on demand.
const files = import.meta.glob('/resources/isometrie/*/*.svg', { query: '?raw', import: 'default' });

const props = defineProps({
  estate: {
    type: String,
    required: true,
  },
  active: {
    type: String,
    default: null,
  },
});

const root = ref(null);
const markup = ref('');

watch(() => props.estate, async (estate) => {
  const loaders = Object.keys(files)
    .filter(path => path.startsWith(`/resources/isometrie/${estate}/`))
    .sort()
    .map(path => files[path]());
  markup.value = (await Promise.all(loaders)).join('');
}, { immediate: true });

watch([markup, () => props.active], () => {
  root.value.querySelectorAll('[data-id].is-visible').forEach(el => el.classList.remove('is-visible'));
  if (props.active) {
    root.value.querySelector(`[data-id="${props.active}"]`)?.classList.add('is-visible');
  }
}, { flush: 'post' });
</script>
