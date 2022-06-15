<template>
  <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
    <g v-if="$props.active" fill="currentColor">
      <path d="M19 21.6V29h2v-7.4l5.4-8.6H13.6z"/>
      <path d="M20 0C9 0 0 9 0 20s9 20 20 20 20-9 20-20S31 0 20 0zm3 22.2V31h-6v-8.8L10 11h20l-7 11.2z"/>
    </g>
    <g v-else>
      <path fill="currentColor" d="M10 11l7 11.2V31h6v-8.8L30 11H10zm11 10.6V29h-2v-7.4L13.6 13h12.8L21 21.6z"/>
    </g>
  </svg>
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