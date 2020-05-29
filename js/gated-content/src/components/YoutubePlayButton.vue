<template>
  <button type="button" aria-label="Play video" class="y-video__button">
    <svg viewBox="0 0 68 48" width="100%" height="100%">
      <path d="M66.5 7.7c-.8-2.9-2.5-5.4-5.4-6.2C55.8.1 34 0 34
                  0S12.2.1 6.9 1.6c-3 .7-4.6 3.2-5.4 6.1a89.6 89.6 0 0 0 0
                  32.5c.8 3 2.5 5.5 5.4 6.3C12.2 47.9 34 48 34 48s21.8-.1
                  27.1-1.6c3-.7 4.6-3.2 5.4-6.1C68 35 68 24 68 24s0-11-1.5-16.3z"
            class="y-video__button-shape"></path>
      <path d="M45 24L27 14v20" class="y-video__button-icon">
      </path></svg>
  </button>
</template>

<script>
export default {
  name: 'YoutubePlayButton',
};
</script>
