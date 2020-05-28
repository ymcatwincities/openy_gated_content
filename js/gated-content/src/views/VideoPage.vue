<template>
  <div class="gated-content-video-page">
    <div class="text-center my-4">
      <router-link :to="{ name: 'Home' }">Home</router-link>
    </div>
    Video page.
    <div v-if="loading">Loading</div>
    <div v-else-if="error">Error loading</div>
    <div v-else>
      <div class="video">
        <LazyYoutubeVideo
          :src="'https://www.youtube.com/embed/' + video.attributes.field_gc_video_media.field_media_video_id"
        />
      </div>
      <div class="video-footer">
        <div>
          <div class="video-footer__title">{{ video.attributes.title }}</div>
          <div class="video-footer__description"
               v-html="video.attributes.field_gc_video_description.processed"
          ></div>
        </div>
        <div>
          <div class="video-footer__block">
            <div class="video-level">{{ video.attributes.field_gc_video_level.name | first_letter }}</div>
            {{ video.attributes.field_gc_video_level.name | capitalize }}
          </div>
          <div class="video-footer__block">
            {{ video.attributes.field_gc_video_instructor }}
          </div>
          <div class="video-footer__block">
            {{ video.attributes.field_gc_video_category.name }}
          </div>
          <div class="video-footer__equipment">Equipment:
            <ul>
              <li v-for="equip in video.attributes.field_gc_video_equipment"
                  :key="equip.drupal_internal__tid">
                {{ equip.name }}
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="video-category">
        < {{ video.attributes.field_gc_video_category.name }}
      </div>

      <div class="video-up-next">
        <div class="header">
          UP NEXT
        </div>

        <div class="list">
          <div>
            <div class="preview">
              <button type="button" aria-label="Play video" class="y-video__button"><svg viewBox="0 0 68 48" width="100%" height="100%"><path d="M66.5 7.7c-.8-2.9-2.5-5.4-5.4-6.2C55.8.1 34 0 34 0S12.2.1 6.9 1.6c-3 .7-4.6 3.2-5.4 6.1a89.6 89.6 0 0 0 0 32.5c.8 3 2.5 5.5 5.4 6.3C12.2 47.9 34 48 34 48s21.8-.1 27.1-1.6c3-.7 4.6-3.2 5.4-6.1C68 35 68 24 68 24s0-11-1.5-16.3z" class="y-video__button-shape"></path><path d="M45 24L27 14v20" class="y-video__button-icon"></path></svg></button>
              <div class="duration">00:00</div>
            </div>
            <div class="meta">
              <div class="video-level">M</div> Moderate
            </div>
          </div>

          <div>
            <div class="preview">
              <button type="button" aria-label="Play video" class="y-video__button"><svg viewBox="0 0 68 48" width="100%" height="100%"><path d="M66.5 7.7c-.8-2.9-2.5-5.4-5.4-6.2C55.8.1 34 0 34 0S12.2.1 6.9 1.6c-3 .7-4.6 3.2-5.4 6.1a89.6 89.6 0 0 0 0 32.5c.8 3 2.5 5.5 5.4 6.3C12.2 47.9 34 48 34 48s21.8-.1 27.1-1.6c3-.7 4.6-3.2 5.4-6.1C68 35 68 24 68 24s0-11-1.5-16.3z" class="y-video__button-shape"></path><path d="M45 24L27 14v20" class="y-video__button-icon"></path></svg></button>
              <div class="duration">00:00</div>
            </div>
            <div class="meta">
              <div class="video-level">M</div> Moderate
            </div>
          </div>

          <div>
            <div class="preview">
              <button type="button" aria-label="Play video" class="y-video__button"><svg viewBox="0 0 68 48" width="100%" height="100%"><path d="M66.5 7.7c-.8-2.9-2.5-5.4-5.4-6.2C55.8.1 34 0 34 0S12.2.1 6.9 1.6c-3 .7-4.6 3.2-5.4 6.1a89.6 89.6 0 0 0 0 32.5c.8 3 2.5 5.5 5.4 6.3C12.2 47.9 34 48 34 48s21.8-.1 27.1-1.6c3-.7 4.6-3.2 5.4-6.1C68 35 68 24 68 24s0-11-1.5-16.3z" class="y-video__button-shape"></path><path d="M45 24L27 14v20" class="y-video__button-icon"></path></svg></button>
              <div class="duration">00:00</div>
            </div>
            <div class="meta">
              <div class="video-level">M</div> Moderate
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script>
import client from '@/client';
import 'vue-lazy-youtube-video/dist/style.css';
import LazyYoutubeVideo from 'vue-lazy-youtube-video';

export default {
  name: 'VideoPage',
  components: {
    LazyYoutubeVideo,
  },
  props: {
    id: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      error: false,
      video: null,
      response: null,
      params: [
        'field_gc_video_category',
        'field_gc_video_media',
        'field_gc_video_equipment',
        'field_gc_video_level',
      ],
    };
  },
  mounted() {
    const params = {};
    if (this.params) {
      params.include = this.params.join(',');
    }
    client
      .get(`jsonapi/node/gc_video/${this.id}`, { params })
      .then((response) => {
        this.loading = false;
        this.video = response.data.data;
        this.combine(response.data);
      })
      .catch((error) => {
        this.error = true;
        this.loading = false;
        console.error(error);
        throw error;
      });
  },
  methods: {
    combine(data) {
      if (!data.included) return;
      this.params.forEach((field) => {
        const rel = data.data.relationships[field].data;
        // Multi-value fields.
        if (Array.isArray(rel)) {
          this.video.attributes[field] = [];
          rel.forEach((relItem) => {
            this.video.attributes[field].push(
              data.included
                .find((obj) => obj.type === relItem.type && obj.id === relItem.id)
                .attributes,
            );
          });
        } else {
          // Single-value fields.
          this.video.attributes[field] = data.included
            .find((obj) => obj.type === rel.type && obj.id === rel.id)
            .attributes;
        }
      });
    },
  },
  filters: {
    capitalize(value) {
      if (!value) return '';
      value = value.toString().toLowerCase();
      return value.charAt(0).toUpperCase() + value.slice(1);
    },
    first_letter(value) {
      if (!value) return '';
      return value.charAt(0).toUpperCase();
    },
  },
};
</script>

<style>

</style>
