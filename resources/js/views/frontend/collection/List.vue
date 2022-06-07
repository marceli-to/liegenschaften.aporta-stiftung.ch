<template>
  <h1>Liste</h1>
</template>
<script>
export default {

  mounted(){
    console.log(this.$parent.$props.uuid);
  }
}
</script>