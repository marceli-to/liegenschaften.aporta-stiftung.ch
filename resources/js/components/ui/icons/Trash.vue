<template>
<div>
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" v-if="$props.size == 'lg'"><path fill="currentColor" d="M6 6h1v7H6zM9 6h1v7H9z"/><path fill="currentColor" d="M16 3h-3.5V0h-9v3H0v1.5h2V16h12V4.5h2V3zM5 1.5h6V3H5V1.5zm7.5 13h-9v-10h9v10z"/></svg>
  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" v-if="$props.size == 'md'"><path fill="currentColor" d="M12 2.4H9V0H3v2.4H0v1.2h1.5V12h9V3.6H12V2.4zM4.2 1.2h3.6v1.2H4.2V1.2zm5.1 9.6H2.7V3.6h6.6v7.2z"/><path fill="currentColor" d="M4.4 4.6h.8v5.2h-.8zM6.8 4.6h.8v5.2h-.8z"/></svg>
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
