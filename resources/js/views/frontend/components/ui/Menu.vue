<template>
<div class="mb-15x">
  <nav class="page-menu-collection content-block">
    <ul>
      <li class="span-4">
        <router-link :to="{name: 'collection-list', params: { uuid: $props.uuid }}">
          <icon-arrow-left :size="'md'" />
          <span>Zurück zur Übersicht</span>
        </router-link>
      </li>
      <li class="span-2 page-menu-collection__browse">
        <router-link :to="{name: 'collection-show', params: { uuid: ''  }}" class="icon-browse">
          <icon-arrow-left :size="'md'" />
        </router-link>
          <span>{{1 | padStart}} / {{2 | padStart}}</span>
        <router-link :to="{name: 'collection-show', params: { uuid: '' }}" class="icon-browse">
          <icon-arrow-right :size="'md'" />
        </router-link>
      </li>
      <li class="span-4">
        <router-link :to="{name: 'collection-list', params: { uuid: $props.uuid }}" class=" flex justify-end">
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
    uuid: [ String, Number ],
  },

  data() {
    return {

      // States
      isLoading: false,
      isFetched: false,

    }
  },

}
</script>