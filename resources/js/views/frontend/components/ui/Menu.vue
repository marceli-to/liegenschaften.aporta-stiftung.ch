<template>
<div class="mb-15x">
  <nav class="page-menu-collection content-block">
    <ul>
      <li :class="[pagination.count > 1 ? 'span-4' : 'span-5']">
        <router-link :to="{name: 'collection-list', params: { uuid: $route.params.uuid }}">
          <icon-arrow-left :size="'md'" />
          <span>Zurück zur Übersicht</span>
        </router-link>
      </li>
      <li class="span-2 page-menu-collection__browse" v-if="pagination.count > 1">
        <router-link :to="{name: 'collection-show', params: { uuid: $route.params.uuid, itemUuid: pagination.prev }}" class="icon-browse">
          <icon-arrow-left :size="'md'" />
        </router-link>
          <span>{{ pagination.index | padStart}} / {{ pagination.count | padStart }}</span>
        <router-link :to="{name: 'collection-show', params: { uuid: $route.params.uuid, itemUuid: pagination.next }}" class="icon-browse">
          <icon-arrow-right :size="'md'" />
        </router-link>
      </li>
      <li :class="[pagination.count > 1 ? 'span-4' : 'span-5']">
        <router-link :to="{name: 'collection-list', params: { uuid: $route.params.uuid }}" class=" flex justify-end">
          <span>Download als PDF</span>
          <icon-document />
        </router-link>
      </li>
    </ul>
  </nav>
</div>
</template>
<script>
import IconArrowLeft from "@/components/ui/icons/ArrowLeft.vue";
import IconArrowRight from "@/components/ui/icons/ArrowRight.vue";
import IconDocument from "@/components/ui/icons/Document.vue";

export default {

  components: {
    IconArrowLeft,
    IconArrowRight,
    IconDocument
  },

  props: {
    pagination: [ Object ],
  },

}
</script>