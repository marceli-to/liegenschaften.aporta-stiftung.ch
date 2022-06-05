<template>
<div>
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" v-if="$props.size == 'lg'"><path fill="currentColor" d="M0 1.4L8.6 10 0 18.6 1.4 20l8.6-8.6 8.6 8.6 1.4-1.4-8.6-8.6L20 1.4 18.6 0 10 8.6 1.4 0z"/></svg>
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" v-if="$props.size == 'md'"><path fill="currentColor" d="M14.9 0L8 6.9 1.1 0 0 1.1 6.9 8 0 14.9 1.1 16 8 9.1l6.9 6.9 1.1-1.1L9.1 8 16 1.1z"/></svg>
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
