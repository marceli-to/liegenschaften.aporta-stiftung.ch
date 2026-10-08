<template>
<div>
  <icon-free v-if="id == 1"></icon-free>
  <icon-reserved v-if="id == 2"></icon-reserved>
  <icon-rented v-if="id == 3"></icon-rented>
  <icon-sold v-if="id == 4"></icon-sold>
</div>
</template>

<script setup>
import IconFree from "@/components/ui/icons/Free.vue";
import IconReserved from "@/components/ui/icons/Reserved.vue";
import IconRented from "@/components/ui/icons/Rented.vue";
import IconSold from "@/components/ui/icons/Sold.vue";

defineProps({
  id: Number
});
</script>
