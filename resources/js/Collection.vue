<template>
  <div>
    <router-view></router-view>
  </div>
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
