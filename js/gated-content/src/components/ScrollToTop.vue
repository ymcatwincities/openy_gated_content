<template>
  <div class="fixed-button scroll-to-top" v-show="visible">
    <a @click.prevent="scrollTop">
      <svg width="100%" height="100%" viewBox="0 0 16 16" fill="currentColor">
        <path
          fill-rule="evenodd"
          d="M8 12a.5.5 0 0 0 .5-.5V5.707l2.146 2.147a.5.5 0 0 0 .708-.708l-3-3a.5.5 0 0 0-.708 0
               l-3 3a.5.5 0 1 0 .708.708L7.5 5.707V11.5a.5.5 0 0 0 .5.5z"
        />
      </svg>
    </a>
  </div>
</template>

<script>
export default {
  data() {
    return {
      visible: false,
    };
  },
  methods: {
    scrollPosition() {
      return window.pageYOffset - document.getElementById('gated-content').offsetTop;
    },
    scrollTop() {
      this.intervalId = setInterval(() => {
        if (this.scrollPosition() <= -100) {
          clearInterval(this.intervalId);
          window.scroll(0, document.getElementById('gated-content').offsetTop);
        }
        window.scroll(0, window.pageYOffset - 100);
      }, 20);
    },
    scrollListener() {
      this.visible = this.scrollPosition() > 250;
    },
  },
  mounted() {
    window.addEventListener('scroll', this.scrollListener);
  },
  beforeDestroy() {
    window.removeEventListener('scroll', this.scrollListener);
  },
};
</script>
