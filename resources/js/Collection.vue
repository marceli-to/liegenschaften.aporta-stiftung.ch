<template>
  <router-view></router-view>
</template>
<script>
export default {
  props: {
    collection: {
      type: Object,
      default: null,
    }
  },
}
</script>
