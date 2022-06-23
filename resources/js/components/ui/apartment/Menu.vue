<template>
<div class="mb-15x">
  <nav class="page-menu content-block">
    <ul>
      <li>
        <router-link :to="{name: 'apartments'}">
          <icon-arrow-left :size="'md'" />
          <span>Zurück</span>
        </router-link>
      </li>
      <li>
        <a href="" @click.prevent="$emit('reset')">
          <icon-reset />
          <span>Zurücksetzen</span>
        </a>
      </li>
      <li>
        <router-link :to="{name: 'apartment-edit', params: { id: $props.id }}" :active-class="'is-active'">
          <icon-pencil />
          <span>Bearbeiten</span>
        </router-link>
      </li>
      <li>
        <a href="" @click.prevent="addToCollection($props.apartment.uuid)" v-if="!isInCollection($props.apartment.uuid)">
          <icon-checkbox class="icon icon-dark" />
          <span>Merken</span>
        </a>
        <a href="" @click.prevent="removeFromCollection($props.apartment.uuid)" v-if="isInCollection($props.apartment.uuid)">
          <icon-checkbox :active="true" class="icon" />
          <span>Merken</span>
        </a> 
      </li>
      <li>
        <a :href="`/assets/media/${$props.apartment.number}-${$props.apartment.uuid}.pdf`" target="_blank">
          <icon-document />
          <span>Download PDF</span>
        </a>
      </li>
    </ul>
    <slot />
  </nav>

</div>
</template>
<script>
import IconArrowLeft from "@/components/ui/icons/ArrowLeft.vue";
import IconReset from "@/components/ui/icons/Reset.vue";
import IconPencil from "@/components/ui/icons/Pencil.vue";
import IconBubble from "@/components/ui/icons/Bubble.vue";
import IconDocument from "@/components/ui/icons/Document.vue";
import IconCheckbox from "@/components/ui/icons/Checkbox.vue";
import Collection from "@/views/backend/pages/mixins/Collection";

export default {

  components: {
    IconArrowLeft,
    IconReset,
    IconPencil,
    IconBubble,
    IconDocument,
    IconCheckbox,
  },

  props: {
    id: [ String, Number ],
    apartment: Object,
  },

  mixins: [Collection],
}
</script>