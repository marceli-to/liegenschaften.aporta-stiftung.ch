<template>
  <select
    :class="classes"
    style="background-position: center right"
    @change="$emit('update:modelValue', $event.target.value)"
    ref="select">
    <slot />
  </select>
</template>
<script>
export default {
  props: ['modelValue', 'size'],

  emits: ['update:modelValue'],

  created() {
  },

  computed: {
    classes() {
      if (this.$props.size == 'sm') {
        return 'mt-0 block w-full px-0 py-3 pr-0 pl-0 border-b-2 border-gray-100 focus:ring-0 focus:border-dark font-mono text-sm';
      } 
      else {
        return 'mt-0 block w-full px-0 py-3 pr-0 pl-0 border-b-2 border-gray-100 focus:ring-0 focus:border-dark font-mono text-sm';
      }
    },
  }
}
</script>
