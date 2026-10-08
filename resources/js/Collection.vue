<template>
  <div>
    <router-view :key="route.fullPath" />
  </div>
</template>
<script setup>
import { provide } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
  estate: {
    type: String,
    default: null,
  },
  estateKey: {
    type: String,
    default: null,
  },
});

const route = useRoute();

// Header and list show it
provide('estate', props.estate);
provide('estateKey', props.estateKey);
</script>
