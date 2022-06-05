<template>
<div>
  <svg xmlns="http://www.w3.org/2000/svg" width="25" height="20" viewBox="0 0 25 20" v-if="$props.size == 'lg'"><path fill="currentColor" d="M10 20l1.4-1.4L3.8 11H25V9H3.8l7.6-7.6L10 0 0 10z"/></svg>
  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="14" viewBox="0 0 18 14" v-if="$props.size == 'md'"><path fill="currentColor" d="M7 14l1.1-1.1-5.2-5.1H18V6.2H2.9L8.1 1 7 0 0 7z"/></svg>
</div>
</template>
<script>
export default {
  props: {
    size: {
      type: String,
      default: 'lg',
    }
  },
}
</script>
