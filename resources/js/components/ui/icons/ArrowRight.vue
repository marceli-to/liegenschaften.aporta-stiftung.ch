<template>
<div>
  <svg xmlns="http://www.w3.org/2000/svg" width="25" height="20" viewBox="0 0 25 20" v-if="$props.size == 'lg'"><path fill="currentColor" d="M15 20l-1.4-1.4 7.6-7.6H0V9h21.2l-7.6-7.6L15 0l10 10z"/></svg>
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="16" viewBox="0 0 20 16" v-if="$props.size == 'md'"><path fill="currentColor" d="M12 0l-1.1 1.1L17 7.2H0v1.6h16.9l-6.1 6.1L12 16l8-8-8-8"/></svg>
  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="14" viewBox="0 0 18 14" v-if="$props.size == 'sm'"><path fill="currentColor" d="M10.5 0L9.495.975 14.82 6.3H0v1.4h14.82l-5.31 5.31.99.99 7-7-7-7"/></svg>
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
