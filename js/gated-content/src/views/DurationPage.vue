<template>
  <div class="gated-content-duration-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <div class="gated-containerV2 my-40-20 px--20-10 title-wrapper">
        <div>
          <div class="title-inline cachet-book-32-28">{{ duration.attributes.name }}</div>
          <AddToFavorite
            :id="duration.id"
            :type="'taxonomy_term'"
            :bundle="'gc_duration'"
          ></AddToFavorite>
        </div>
      </div>

      <div v-for="component in componentsOrder" :key="component">
        <div class="videos-wrapper" v-if="showComponent.gc_video
          && showOnCurrentIteration('gc_video', component)">
          <VideoListing
            v-if="selectedComponent === 'gc_video' || selectedComponent === 'all'"
            :title="config.components.gc_video.title"
            :pagination="selectedComponent === 'gc_video'"
            :duration="duration.attributes.drupal_internal__tid"
            :viewAll="false"
            :sort="sortData('node', 'gc_video')"
            :limit="viewAllContentMode ? 0 : itemsLimit"
            @listing-not-empty="listingIsNotEmpty('gc_video', ...arguments)"
          >
            <template #filterButton>
              <button
                v-if="selectedComponent === 'all'"
                type="button"
                class="view-all"
                @click="preSelectedComponent = 'gc_video'; applyFilters()">
                More
              </button>
            </template>
          </VideoListing>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import client from '@/client';
import Spinner from '@/components/Spinner.vue';
import AddToFavorite from '@/components/AddToFavorite.vue';
import VideoListing from '@/components/video/VideoListing.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'DurationPage',
  mixins: [JsonApiCombineMixin, SettingsMixin, FilterAndSortMixin],
  components: {
    AddToFavorite,
    Spinner,
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
      duration: null,
      itemsLimit: 8,
      DEFAULT_SORT: 'date_asc',
      showComponent: {
        gc_video: true,
        virtual_meeting: true,
        live_stream: true,
        vy_blog_post: true,
      },
      showLiveStreamViewAll: false,
      showVirtualMeetingViewAll: false,
    };
  },
  watch: {
    id: 'reload',
    '$route.query': 'load',
  },
  async mounted() {
    await this.load();
  },
  methods: {
    async load() {
      this.loading = true;
      client
        .get(`jsonapi/taxonomy_term/gc_duration/${this.id}`)
        .then((response) => {
          this.duration = response.data.data;
          this.loading = false;
        })
        .catch((error) => {
          this.error = true;
          this.loading = false;
          console.error(error);
          throw error;
        });
    },
    listingIsNotEmpty(component, notEmpty) {
      this.showComponent[component] = notEmpty;
    },
    reload() {
      this.showComponent = {
        gc_video: true,
        vy_blog_post: true,
        live_stream: true,
        virtual_meeting: true,
      };
      this.load();
    },
  },
  computed: {
    ...mapGetters([
    ]),
    viewAllContentMode() {
      // Enable viewAllContentMode only when we filter by content.
      return this.selectedComponent !== 'all';
    },
  },
};
</script>
