<template>
<div>
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" v-if="$props.active"><g fill="currentColor"><path d="M0 0v16h16V0H0zm14.5 14.5h-13v-13h13v13z"/><path d="M5.1 12L8 9.1l2.9 2.9 1.1-1.1L9.1 8 12 5.1 10.9 4 8 6.9 5.1 4 4 5.1 6.9 8 4 10.9z"/></g></svg>
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" v-else><path fill="#A1A0A0" d="M1.5 1.5h13v13h-13v-13zM0 16h16V0H0v16z"/></svg>
</div>
</template>
<script>
export default {
  props: {
    active: {
      type: [Boolean, String, Number],
      default: 0,
    }
  },
}
</script>
