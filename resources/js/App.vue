<template>
  <router-view></router-view>
</template>
<script>
export default {
  mounted() {
    this.fetchUser();
  },
  methods: {
    fetchUser() {
      if (!this.$store.state.user) {
        this.axios.get(`/api/user`).then(response => {
          this.$store.commit('user', response.data);
        });
      }
    },
  }
}
</script>