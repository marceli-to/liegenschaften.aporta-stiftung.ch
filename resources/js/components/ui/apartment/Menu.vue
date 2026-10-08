<template>
<div class="mb-15x">
  <nav class="page-menu content-block">
    <ul>
      <li>
        <router-link :to="{name: store.referrer ? store.referrer : 'apartments'}">
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
        <router-link :to="{name: 'apartment-edit', params: { uuid: apartment.uuid }}" :active-class="'is-active'">
          <icon-pencil />
          <span>Bearbeiten</span>
        </router-link>
      </li>
      <li>
        <a href="" @click.prevent="addToCollection(apartment.uuid)" v-if="!isInCollection(apartment.uuid)">
          <icon-checkbox class="icon icon-dark" />
          <span>Merken</span>
        </a>
        <a href="" @click.prevent="removeFromCollection(apartment.uuid)" v-if="isInCollection(apartment.uuid)">
          <icon-checkbox :active="true" class="icon" />
          <span>Merken</span>
        </a> 
      </li>
      <li>
        <a :href="`/assets/media/${apartment.number}-${apartment.uuid}.pdf`" target="_blank">
          <icon-document />
          <span>Download PDF</span>
        </a>
      </li>
    </ul>
    <slot />
  </nav>

</div>
</template>
<script setup>
import { store } from '@/store';
import { useCollection } from '@/composables/useCollection';
import IconArrowLeft from "@/components/ui/icons/ArrowLeft.vue";
import IconReset from "@/components/ui/icons/Reset.vue";
import IconPencil from "@/components/ui/icons/Pencil.vue";
import IconDocument from "@/components/ui/icons/Document.vue";
import IconCheckbox from "@/components/ui/icons/Checkbox.vue";

defineProps({
  apartment: Object,
});

defineEmits(['reset']);

const { addToCollection, removeFromCollection, isInCollection } = useCollection();
</script>
