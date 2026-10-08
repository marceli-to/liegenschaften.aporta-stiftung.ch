<template>
  <Notifications />
  <router-view :key="route.fullPath" />
</template>
<script setup>
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import http from '@/lib/http';
import { store } from '@/store';
import Notifications from '@/components/ui/misc/Notifications.vue';

const route = useRoute();

onMounted(() => {
  if (!store.user) {
    http.get('/api/user').then(response => store.user = response.data);
  }
});
</script>
