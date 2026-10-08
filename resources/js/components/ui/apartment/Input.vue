<template>
  <div :class="[cls ? cls : '']">
    <span>
      <slot />
    </span>
  </div>
</template>
<script setup>
defineProps({
  cls: {
    type: String,
    default: ''
  },
});
</script>
