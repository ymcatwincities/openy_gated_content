<template>
  <iframe
    :src="src"
    width="100%"
    height="100%"
    allow="autoplay; microphone; camera; fullscreen"
    frameborder="0"
    allowFullScreen="true"
    webkitallowfullscreen="true"
    mozallowfullscreen="true"
    scrolling="no"
    seamless="seamless"
  ></iframe>
</template>

<script>
export default {
  name: 'ZoomIframe',
  props: {
    src: {
      type: String,
      required: true,
    },
  },
  watch: {
    src: 'reload',
    $route: 'reload',
  },
  methods: {
    reload() {
      this.$forceUpdate();
    },
  },
};
</script>
