<template>
<div class="mb-15x">
  <nav class="page-menu">
    <ul>
      <li>
        <router-link :to="{name: 'apartments'}">
          <icon-arrow-left />
          <span>Zurück</span>
        </router-link>
      </li>
      <li>
        <a href="">
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
        <a href="">
          <icon-bubble />
          <span>Kommentar</span>
        </a>
      </li>
      <li>
        <a href="">
          <icon-document />
          <span>PDF-Export</span>
        </a>
      </li>
    </ul>
    <slot />
  </nav>
</div>
</template>
<script>
//import DialogWrapper from "@/components/ui/misc/Dialog.vue";
import IconArrowLeft from "@/components/ui/icons/ArrowLeft-sm.vue";
import IconReset from "@/components/ui/icons/Reset.vue";
import IconPencil from "@/components/ui/icons/Pencil.vue";
import IconBubble from "@/components/ui/icons/Bubble.vue";
import IconDocument from "@/components/ui/icons/Document.vue";

export default {

  components: {
    //DialogWrapper,
    IconArrowLeft,
    IconReset,
    IconPencil,
    IconBubble,
    IconDocument,
  },

  props: {
    id: [ String, Number ],
    apartment: Object,
  },

  data() {
    return {

      // States
      isLoading: false,
      isFetched: false,
      
      // Routes
      routes: {
        fetch: '/api/apartment',
      },
    }
  },

  mounted() {
  },
}
</script>