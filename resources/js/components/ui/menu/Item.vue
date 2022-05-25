<template>
  <router-link :to="{name: $props.route}" :active-class="'text-highlight'" class="text-gray-400 hover:text-highlight text-base font-normal no-underline flex items-center p-3 sm:p-5">
    <slot />
  </router-link>
</template>
<script>
export default {
  props: {
    route: {
      type: String,
    }
  }
}
</script>

 