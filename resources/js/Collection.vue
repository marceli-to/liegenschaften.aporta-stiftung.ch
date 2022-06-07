<template>
  <router-view></router-view>
</template>
<script>
export default {
  props: {
    uuid: {
      type: String,
      default: null,
    }
  },
}
</script>
