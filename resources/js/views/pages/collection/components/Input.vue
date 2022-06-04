<template>
  <div :class="[$props.cls ? $props.cls : '']">
    <span>
      <slot />
    </span>
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
