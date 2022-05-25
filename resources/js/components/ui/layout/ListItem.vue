<template>
  <div :class="$props.cls">
    <slot />
  </div>
</template>
<script>
export default {
  props: {
    cls: {
      type: String,
      default: 'span-1'
    },
  }
}
</script>
