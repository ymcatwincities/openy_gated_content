<template>
  <div class="gated-containerV2 cachet-book-32-28 my-40-20 px--20-10 text-gray">
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
