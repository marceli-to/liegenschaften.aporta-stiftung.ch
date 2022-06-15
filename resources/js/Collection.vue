<template>
  <router-view></router-view>
</template>
<script>
export default {
  props: {
    estate: {
      type: String,
      default: null,
    }
  },
}
</script>
