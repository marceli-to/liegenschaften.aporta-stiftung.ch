<template>
<div>
  <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" v-if="$props.active"><path fill="currentColor" d="M19 21.6V29h2v-7.4l5.4-8.6H13.6z"/><path fill="currentColor" d="M20 0C9 0 0 9 0 20s9 20 20 20 20-9 20-20S31 0 20 0zm3 22.2V31h-6v-8.8L10 11h20l-7 11.2z" /></svg>
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" v-else><path fill="currentColor" d="M0 0l7 11.2V20h6v-8.8L20 0H0zm11 10.6V18H9v-7.4L3.6 2h12.8L11 10.6z" /></svg>
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