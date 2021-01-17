<template>
  <div class="gated-content-video-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div class="video-wrapper">
        <div class="video gated-containerV2 px--20-10 pt-40-20">
          <MediaPlayer
            :media="video.attributes.field_gc_video_media"
            @playerEvent="logPlaybackEvent($event)"
          />
        </div>
      </div>
      <div class="video-footer-wrapper bg-black">
        <div class="video-footer gated-containerV2 text-white px--20-10 py-40-20">
          <div class="pb-20-10 cachet-book-32-28">{{ video.attributes.title }}</div>
          <div class="video-footer__fav pb-40-20">
            <AddToFavorite
              :id="video.attributes.drupal_internal__nid"
              :type="'node'"
              :bundle="'gc_video'"
              class="rounded-border border-thunder white"
            ></AddToFavorite>
            <div class="timer">
              {{ video_length }}
            </div>
          </div>
          <div class="verdana-14-12">
            <div
              v-if="video.attributes.field_gc_video_instructor"
              class="video-footer__block">
              <SvgIcon icon="instructor-icon"
                       class="fill-white"
                       :growByHeight=false></SvgIcon>
              {{ video.attributes.field_gc_video_instructor }}
            </div>
            <div
              v-if="video.attributes.field_gc_video_category &&
              video.attributes.field_gc_video_category.length > 0"
              class="video-footer__block video-footer__category">
              <SvgIcon icon="categories"
                       class="fill-white"
                       :growByHeight=false></SvgIcon>
              <span v-for="(category, index) in video.attributes.field_gc_video_category"
                    :key="category.drupal_internal__tid">
                <router-link :to="{
                  name: 'Category',
                  params: {
                    cid: video.relationships.field_gc_video_category.data[index].id,
                    type: 'video'
                  }
                }">
                  {{ category.name }}
                </router-link>
              </span>
            </div>
            <div
              v-if="video.attributes.field_gc_video_equipment.length > 0"
              class="video-footer__block">
              <SvgIcon icon="cubes-solid" class="fill-white" :growByHeight=false></SvgIcon>
              Equipment:
            </div>
            <ul class="video-footer__equipment">
              <li v-for="equip in video.attributes.field_gc_video_equipment"
                  :key="equip.drupal_internal__tid">
                {{ equip.name }}
              </li>
            </ul>
          </div>
          <div
            v-if="video.attributes.field_gc_video_description"
            class="verdana-16-14"
            v-html="video.attributes.field_gc_video_description.processed"
          ></div>
        </div>
      </div>
      <VideoListing
        v-if="firstCategory"
        :title="config.components.gc_video.up_next_title"
        :excluded-video-id="video.id"
        :category="firstCategory"
        :viewAll="true"
        :limit="8"
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
import SvgIcon from '@/components/SvgIcon.vue';
import moment from 'moment';

export default {
  name: 'VideoPage',
  mixins: [JsonApiCombineMixin, SettingsMixin],
  components: {
    SvgIcon,
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
    video_length() {
      return moment.duration(this.video.attributes.field_gc_video_duration, 'seconds').format('m [minute]');
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
