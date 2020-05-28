<template>
  <div class="gated-content-video-page">
    <div class="text-center my-4">
      <router-link :to="{ name: 'Home' }">Home</router-link>
    </div>
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
            <div class="video-level">
              {{ video.attributes.field_gc_video_level.name | first_letter }}
            </div>
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
        &lt; {{ video.attributes.field_gc_video_category.name }}
      </div>

      <div class="video-up-next">
        <div class="header">
          UP NEXT
        </div>

        <div class="list">
          <div v-for="item in [1,2,3,4]" :key="item">
            <div class="preview" v-bind:style="{
              backgroundImage: 'url(//i.ytimg.com/vi/B2bW0DIs0hA/maxresdefault.jpg)'
            }">
              <YoutubePlayButton></YoutubePlayButton>
              <div class="duration">00:0{{item}}</div>
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
import YoutubePlayButton from '../components/YoutubePlayButton.vue';

export default {
  name: 'VideoPage',
  components: {
    LazyYoutubeVideo,
    YoutubePlayButton,
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
      const newValue = value.toString().toLowerCase();
      return newValue.charAt(0).toUpperCase() + newValue.slice(1);
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
