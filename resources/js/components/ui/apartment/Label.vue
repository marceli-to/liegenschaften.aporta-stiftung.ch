<template>
  <div :class="[$props.cls ? $props.cls : '']">
    <label>
      <slot />
    </label>
  </div>
</template>
<script>
export default {
  props: {
    cls: {
      type: String,
      default: ''
    },
  },
}
</script>