<template>
<div>
  <header :class="cls">
    <div>
      <nav class="site-menu">
        <ul>
          <li class="xs:span-4 span-2">
            <icon-logo class="icon-logo" />
          </li>
          <li class="xs:hide span-6 page-title">
            <h1>{{ estate }}</h1>
          </li>
          <li class="xs:span-8 span-4 page-title flex justify-end">
            <h2>Ihre Wohnungsangebote</h2>
          </li>
        </ul>
      </nav>
    </div>
  </header>
  <slot />
</div>
</template>
<script setup>
import { computed, inject } from 'vue';
import IconLogo from "@/components/ui/icons/Logo.vue";

const props = defineProps({
  view: {
    type: String,
    default: 'list'
  },
});

const estate = inject('estate');

const cls = computed(() => {
  let cls = 'site-header';
  if (props.view == 'show') {
    cls = cls + ' is-collection-detail';
  }
  if (props.view == 'list') {
    cls = cls + ' is-collection-list';
  }
  return cls;
});
</script>
