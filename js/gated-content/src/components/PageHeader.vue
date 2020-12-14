<template>
  <div class="page-header">
    {{ title }}
  </div>
</template>

<script>
export default {
  name: 'PageHeader',
  props: {
    title: String,
  },
};
</script>
