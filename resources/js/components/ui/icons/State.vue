<template>
<div>
  <icon-mail v-if="$props.id == 1"></icon-mail>
  <icon-emblem v-if="$props.id == 2"></icon-emblem>
  <icon-stamp v-if="$props.id == 3"></icon-stamp>
  <icon-emblem-cross v-if="$props.id == 4"></icon-emblem-cross>
  <icon-cross-hair v-if="$props.id == 5"></icon-cross-hair>
  <icon-checkmark v-if="$props.id == 6"></icon-checkmark>
  <icon-hourglass v-if="$props.id == 7"></icon-hourglass>
</div>
</template>

<script>
import IconMail from "@/components/ui/icons/Mail.vue";
import IconHourglass from "@/components/ui/icons/Hourglass.vue";
import IconEmblem from "@/components/ui/icons/Emblem.vue";
import IconStamp from "@/components/ui/icons/Stamp.vue";
import IconEmblemCross from "@/components/ui/icons/EmblemCross.vue";
import IconCrossHair from "@/components/ui/icons/CrossHair.vue";
import IconCheckmark from "@/components/ui/icons/Checkmark.vue";

export default {

  components: {
    IconMail,
    IconEmblem,
    IconStamp,
    IconEmblemCross,
    IconCrossHair,
    IconCheckmark,
    IconHourglass,
  },

  props: {
    id: Number
  },

}
</script>
