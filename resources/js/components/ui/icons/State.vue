<template>
<div>
  <icon-free v-if="$props.id == 1"></icon-free>
  <icon-reserved v-if="$props.id == 2"></icon-reserved>
  <icon-rented v-if="$props.id == 3"></icon-rented>
  <icon-sold v-if="$props.id == 4"></icon-sold>
</div>
</template>

<script>
import IconFree from "@/components/ui/icons/Free.vue";
import IconReserved from "@/components/ui/icons/Reserved.vue";
import IconRented from "@/components/ui/icons/Rented.vue";
import IconSold from "@/components/ui/icons/Sold.vue";

export default {

  components: {
    IconFree,
    IconReserved,
    IconRented,
    IconSold
  },

  props: {
    id: Number
  },

}
</script>
