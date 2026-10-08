<template>
  <div :class="[cls ? cls : '']">
    <label>
      <slot />
    </label>
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
