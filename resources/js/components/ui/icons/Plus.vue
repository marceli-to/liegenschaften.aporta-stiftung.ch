<template>
<div>
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" v-if="$props.size == 'lg'"><path fill="currentColor" d="M20 9h-9V0H9v9H0v2h9v9h2v-9h9z"/></svg>
  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" v-if="$props.size == 'md'"><path fill="currentColor" d="M5.4 0v5.4H0v1.2h5.4V12h1.2V6.6H12V5.4H6.6V0z"/></svg>
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
