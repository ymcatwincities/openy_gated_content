<template>
  <div class="gated-content-video-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div class="video-wrapper">
        <div class="video gated-container">
          <MediaPlayer
            :media="video.attributes.field_gc_video_media"
            @playerEvent="logPlaybackEvent($event)"
          />
        </div>
      </div>
      <div class="video-footer-wrapper">
        <div class="video-footer gated-container">
          <div>
            <div class="video-footer__title">{{ video.attributes.title }}</div>
            <div
              v-if="video.attributes.field_gc_video_description"
              class="video-footer__description"
                 v-html="video.attributes.field_gc_video_description.processed"
            ></div>
            <AddToFavorite
              :id="video.attributes.drupal_internal__nid"
              :type="'node'"
              :bundle="'gc_video'"
            ></AddToFavorite>
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
            <div
              v-if="video.attributes.field_gc_video_category &&
              video.attributes.field_gc_video_category.length > 0"
              class="video-footer__block video-footer__category">
              Category:
              <span v-for="category in video.attributes.field_gc_video_category"
                    :key="category.drupal_internal__tid">{{ category.name }}</span>
            </div>
            <div
              v-if="video.attributes.field_gc_video_equipment.length > 0"
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
      <div
        v-if="video.attributes.field_gc_video_category &&
        video.attributes.field_gc_video_category.length > 0"
        class="video-category-wrapper">
        <div class="gated-container video-category">
          <span v-for="(category_data, index) in video.relationships.field_gc_video_category.data"
                :key="index">
            <router-link :to="{
              name: 'Category',
              params: {
                cid: category_data.id,
                type: 'video'
              }
            }">{{ video.attributes.field_gc_video_category[index].name }}</router-link>
          </span>
        </div>
      </div>
      <VideoListing
        v-if="firstCategory"
        :title="config.components.gc_video.up_next_title"
        :excluded-video-id="video.id"
        :category="firstCategory"
        :viewAll="true"
        :limit="6"
      />
    </template>
  </div>
</template>

<script>
import client from '@/client';
import AddToFavorite from '@/components/AddToFavorite.vue';
import Spinner from '@/components/Spinner.vue';
import VideoListing from '@/components/video/VideoListing.vue';
import MediaPlayer from '@/components/MediaPlayer.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'VideoPage',
  mixins: [JsonApiCombineMixin, SettingsMixin],
  components: {
    MediaPlayer,
    VideoListing,
    Spinner,
    AddToFavorite,
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
  computed: {
    firstCategory() {
      if (
        !this.video.relationships.field_gc_video_category.data
        || this.video.relationships.field_gc_video_category.data.length === 0
      ) {
        return null;
      }
      return this.video.relationships.field_gc_video_category.data[0].id;
    },
  },
  methods: {
    async load() {
      this.loading = true;
      const params = {};
      if (this.params) {
        params.include = this.params.join(',');
      }
      client
        .get(`jsonapi/node/gc_video/${this.id}`, { params })
        .then((response) => {
          this.video = this.combine(response.data.data, response.data.included, this.params);
          this.loading = false;
        }).then(() => {
          this.logPlaybackEvent('entityView');
        })
        .catch((error) => {
          this.error = true;
          this.loading = false;
          console.error(error);
          throw error;
        });
    },
    logPlaybackEvent(eventType) {
      this.$log.trackEvent(eventType, 'node', 'gc_video', this.video.attributes.drupal_internal__nid);
    },
  },
};
</script>
