<template>
  <div class="dialog" v-if="isOpen" @click="hide()">
    <div class="dialog__inner">
      <template v-if="$slots.message">
        <div class="message">
          <slot name="message" />
        </div>
      </template>
      <template v-if="$slots.actions">
        <div class="actions">
          <slot name="actions" />
          <a href="javascript:;" class="btn-secondary is-outline" @click="hide()">Abbrechen</a>
        </div>
      </template>
      <template v-if="$slots.button">
        <div class="actions">
          <slot name="button" />
        </div>
      </template>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const isOpen = ref(false);

function show() {
  isOpen.value = true;
}

function hide() {
  isOpen.value = false;
}

function onEscape(e) {
  if (isOpen.value && e.key === 'Escape') {
    hide();
  }
}

onMounted(() => document.addEventListener('keydown', onEscape));
onBeforeUnmount(() => document.removeEventListener('keydown', onEscape));

defineExpose({ show, hide });
</script>
