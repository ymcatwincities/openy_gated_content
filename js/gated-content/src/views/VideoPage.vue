<template>
  <div class="gated-content-video-page">
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
          <div
            v-if="video.attributes.field_gc_video_description"
            class="video-footer__description"
               v-html="video.attributes.field_gc_video_description.processed"
          ></div>
        </div>
        <div>
          <div
            v-if="video.attributes.field_gc_video_level"
            class="video-footer__block">
            <div class="video-level">
              {{ video.attributes.field_gc_video_level.name | first_letter }}
            </div>
            {{ video.attributes.field_gc_video_level.name | capitalize }}
          </div>
          <div
            v-if="video.attributes.field_gc_video_instructor"
            class="video-footer__block">
            <i class="fa fa-user"></i>
            {{ video.attributes.field_gc_video_instructor }}
          </div>
          <div class="video-footer__block">
            <i class="fa fa-hand-o-right"></i>
            {{ video.attributes.field_gc_video_category.name }}
          </div>
          <div
            v-if="video.attributes.field_gc_video_equipment"
            class="video-footer__equipment">
            <i class="fa fa-cubes"></i>
            Equipment:
            <ul>
              <li v-for="equip in video.attributes.field_gc_video_equipment"
                  :key="equip.drupal_internal__tid">
                {{ equip.name }}
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!--div class="video-category">
        &lt; {{ video.attributes.field_gc_video_category.name }}
      </div-->
      <VideoListing class="videos"
        :title="'UP NEXT'"
        :excluded-video-id="video.id"
      />
    </div>
  </div>
</template>

<script>
import client from '@/client';
import 'vue-lazy-youtube-video/dist/style.css';
import LazyYoutubeVideo from 'vue-lazy-youtube-video';
import VideoListing from '../components/VideoListing.vue';
import { JsonApiCombineMixin } from '../mixins/JsonApiCombineMixin';

export default {
  name: 'VideoPage',
  mixins: [JsonApiCombineMixin],
  components: {
    LazyYoutubeVideo,
    VideoListing,
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
  watch: {
    $route: 'load',
  },
  async mounted() {
    await this.load();
  },
  methods: {
    async load() {
      const params = {};
      if (this.params) {
        params.include = this.params.join(',');
      }
      client
        .get(`jsonapi/node/gc_video/${this.id}`, { params })
        .then((response) => {
          this.video = this.combine(response.data.data, response.data.included, this.params);
          this.loading = false;
        })
        .catch((error) => {
          this.error = true;
          this.loading = false;
          console.error(error);
          throw error;
        });
    },
  },
};
</script>

<style>

</style>
