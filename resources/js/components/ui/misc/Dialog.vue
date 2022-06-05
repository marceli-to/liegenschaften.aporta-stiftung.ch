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
<script>
export default {
  data() {
    return {
      isOpen: false,
    }
  },

  created() {
    const onEscape = (e) => {
      if (this.isOpen && e.keyCode === 27) {
        this.hide();
      }
    }
    document.addEventListener('keydown', onEscape);
  },

  methods: {

    show() {
      this.isOpen = true;
    },

    hide() {
      this.isOpen = false;
    }
  }
}
</script>