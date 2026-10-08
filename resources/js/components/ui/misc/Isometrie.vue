<template>
<div :class="['iso-wrapper', count > 1 ? 'is-split' : '']" ref="root" v-html="markup"></div>
</template>
<script setup>
import { ref, watch } from 'vue';

// One or more SVGs per estate (resources/isometrie/{estate}/*.svg, e.g. one
// per building, shown side by side), each apartment a [data-id] group.
// Loaded on demand.
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
  // Detail views: only the SVG (building) with the active apartment
  focus: {
    type: Boolean,
    default: false,
  },
});

const root = ref(null);
const markup = ref('');
const count = ref(0);

watch(() => props.estate, async (estate) => {
  const loaders = Object.keys(files)
    .filter(path => path.startsWith(`/resources/isometrie/${estate}/`))
    .sort()
    .map(path => files[path]());
  markup.value = (await Promise.all(loaders)).join('');
  count.value = loaders.length;
}, { immediate: true });

// Side by side at one scale: each as wide as its drawing
watch(markup, () => {
  root.value.querySelectorAll('svg.iso').forEach(svg => svg.style.flexGrow = svg.viewBox.baseVal.width);
}, { flush: 'post' });

watch([markup, () => props.active], () => {
  root.value.querySelectorAll('[data-id].is-visible').forEach(el => el.classList.remove('is-visible'));
  if (props.active) {
    root.value.querySelector(`[data-id="${props.active}"]`)?.classList.add('is-visible');
  }
  if (props.focus && props.active) {
    root.value.querySelectorAll('svg.iso').forEach(svg => svg.style.display = svg.querySelector(`[data-id="${props.active}"]`) ? '' : 'none');
  }
}, { flush: 'post' });
</script>
