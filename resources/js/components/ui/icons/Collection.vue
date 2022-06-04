<template>
<div>
  <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" v-if="!$props.active"><path fill="white" d="M28 28H12V12h6v-2h-8v20h20v-8h-2z"/><path fill="white" d="M22 10v2h4.727L20 18.727 21.273 20 28 13.273V18h2v-8z"/></svg>
  <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" v-else><path fill="white" d="M20 0C8.954 0 0 8.955 0 20s8.954 20 20 20 20-8.955 20-20S31.046 0 20 0zm10 30H10V10h8v2h-6v16h16v-6h2v8zm0-12h-2v-4.727L21.273 20 20 18.727 26.727 12H22v-2h8v8z"/></svg>
</div>
</template>
<script>
export default {
  props: {
    active: {
      type: Boolean,
      default: false,
    }
  },
}
</script>