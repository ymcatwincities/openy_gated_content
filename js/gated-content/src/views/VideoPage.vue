<template>
  <div class="gated-content-video-page">
    <div v-if="loading">Loading</div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div class="video-wrapper">
        <div class="video">
          <MediaPlayer :media="video.attributes.field_gc_video_media"/>
        </div>
      </div>
      <div class="video-footer-wrapper">
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
              Level: {{ video.attributes.field_gc_video_level.name | capitalize }}
            </div>
            <div
              v-if="video.attributes.field_gc_video_instructor"
              class="video-footer__block">
              Instructor: {{ video.attributes.field_gc_video_instructor }}
            </div>
            <div class="video-footer__block">
              Category: {{ video.attributes.field_gc_video_category.name }}
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
      </div>
      <div class="video-category-wrapper">
        <div class="video-category">
          <router-link :to="{
            name: 'Category',
            params: {
              cid: video.relationships.field_gc_video_category.data.id
            }
          }">
            {{ video.attributes.field_gc_video_category.name }}
          </router-link>
        </div>
      </div>
      <VideoListing class="videos"
        :title="'UP NEXT'"
        :excluded-video-id="video.id"
        :category="video.relationships.field_gc_video_category.data.id"
        :viewAll="true"
      />
    </template>
  </div>
</template>

<script>
import client from '@/client';
import VideoListing from '../components/VideoListing.vue';
import MediaPlayer from '../components/MediaPlayer.vue';
import { JsonApiCombineMixin } from '../mixins/JsonApiCombineMixin';

export default {
  name: 'VideoPage',
  mixins: [JsonApiCombineMixin],
  components: {
    MediaPlayer,
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
