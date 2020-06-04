<template>
  <div class="gated-content-video-page">
    <div v-if="loading">Loading</div>
    <div v-else-if="error">Error loading</div>
    <div v-else>
      <div class="video">
        <LazyYoutubeVideo
          :src="'https://www.youtube.com/embed/' + media.field_media_video_id"
        />
      </div>
      <div class="video-footer">
        <div>
          <div class="video-footer__title">{{ video.attributes.title }}</div>
          <div
            v-if="video.attributes.description"
            class="video-footer__description"
               v-html="video.attributes.description.processed"
          ></div>
        </div>
        <div>
          <div class="video-footer__block">
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            {{ video.attributes.date.value | month }}
            {{ video.attributes.date.value | day }},
            {{ video.attributes.date | schedule }}
          </div>
          <div class="video-footer__block"
            v-if="level"
          >
            <div class="video-level">
              {{ level | first_letter }}
            </div>
            {{ level | capitalize }}
          </div>
          <div class="video-footer__block" v-if="video.attributes.instructor">
            <i class="fa fa-user"></i>
            {{ video.attributes.instructor }}
          </div>
          <div class="video-footer__block">
            <i class="fa fa-hand-o-right"></i>
            {{ category }}
          </div>
          <div class="video-footer__equipment"
            v-if="video.attributes.equipment"
          >
            <i class="fa fa-cubes"></i>
            Equipment:
            <ul>
              <li>
                {{ video.attributes.equipment }}
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!--div class="video-category">
        &lt; {{ video.attributes.field_gc_video_category.name }}
      </div-->
      <LiveStreamListing class="videos"
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
import LiveStreamListing from '../components/LiveStreamListing.vue';
import { JsonApiCombineMixin } from '../mixins/JsonApiCombineMixin';

export default {
  name: 'LiveStreamPage',
  mixins: [JsonApiCombineMixin],
  components: {
    LazyYoutubeVideo,
    LiveStreamListing,
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
        'field_ls_category',
        'field_ls_media',
        'field_ls_level',
        // Data from parent (series).
        'category',
        'media',
        'level',
      ],
    };
  },
  computed: {
    // This values most of all from parent (series), but can be overridden by item,
    // so ve need to check this here and use correct value.
    level() {
      return this.video.attributes.field_ls_level ? this.video.attributes.field_ls_level.name
        : this.video.attributes.level.name;
    },
    media() {
      return this.video.attributes.field_ls_media ? this.video.attributes.field_ls_media
        : this.video.attributes.media;
    },
    category() {
      return this.video.attributes.field_ls_category ? this.video.attributes.field_ls_category.name
        : this.video.attributes.category.name;
    },
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
        .get(`jsonapi/eventinstance/live_stream/${this.id}`, { params })
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
