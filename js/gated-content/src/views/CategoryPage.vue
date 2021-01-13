<template>
  <div class="gated-content-category-page">
    <div v-if="loading" class="text-center">
      <Spinner></Spinner>
    </div>
    <div v-else-if="error">Error loading</div>
    <template v-else>
      <Modal v-if="showModal" @close="showModal = false" class="adjust-modal">
        <template v-slot:header>
          <h3>Filter</h3>
        </template>
        <template v-slot:body>
          <div class="filter">
            <h4>Content types</h4>
            <div class="form-check" v-for="option in contentTypeOptions" v-bind:key="option.value">
              <label :for="option.value">
                <input
                  type="radio"
                  :id="option.value"
                  :value="option.value"
                  :disabled="option.value !== 'all' && !showComponent[option.value]"
                  autocomplete="off"
                  v-model="preSelectedComponent"
                >
                <span class="checkmark"></span>
                <span class="caption">{{ option.label }}</span>
              </label>
            </div>
          </div>
          <div class="sort">
            <h4>Sort order</h4>
            <div class="form-check" v-for="option in filterOptions" v-bind:key="option.value">
              <label :for="option.value">
                <input
                  type="radio"
                  :id="option.value"
                  :value="option.value"
                  autocomplete="off"
                  v-model="preSelectedSort"
                >
                <span class="checkmark"></span>
                <span class="caption">{{ option.label }}</span>
              </label>
            </div>
          </div>
        </template>
        <template v-slot:footer>
          <button type="button" class="btn btn-outline-primary" @click="showModal = false">
            Cancel
          </button>
          <button type="button" class="btn btn-primary" @click="applyFilters">Apply</button>
        </template>
      </Modal>

      <div class="gated-containerV2 my-40-20 px--20-10 title-wrapper">
        <div>
          <div class="title title-inline cachet-book-32-28 text-gray">
            {{ category.attributes.name }}
          </div>
          <AddToFavorite
            :id="category.attributes.drupal_internal__tid"
            :type="'taxonomy_term'"
            :bundle="'gc_category'"
          ></AddToFavorite>
        </div>
        <button type="button"
                class="adjust-button" @click="showModal = true">Filter</button>
      </div>

      <div class="live-stream-wrapper" v-if="showComponent.live_stream">
        <EventListing
          v-if="selectedComponent === 'live_stream' || selectedComponent === 'all'"
          :title="config.components.live_stream.title"
          :category="category.id"
          :msg="'Live streams not found.'"
          :sort="sortData('eventinstance')"
          :limit="viewAllContentMode ? 50 : itemsLimit"
          @listing-not-empty="listingIsNotEmpty('live_stream', ...arguments)"
        >
          <template #filterButton>
            <button
              v-if="selectedComponent === 'all'"
              type="button"
              class="view-all"
              @click="preSelectedComponent = 'live_stream'; applyFilters()">
              More
            </button>
          </template>
        </EventListing>
      </div>

      <div class="virtual-meeting-wrapper" v-if="showComponent.virtual_meeting">
        <EventListing
          v-if="selectedComponent === 'virtual_meeting' || selectedComponent === 'all'"
          :title="config.components.virtual_meeting.title"
          :category="category.id"
          :eventType="'virtual_meeting'"
          :msg="'Virtual Meetings not found.'"
          :sort="sortData('eventinstance')"
          :limit="viewAllContentMode ? 50 : itemsLimit"
          @listing-not-empty="listingIsNotEmpty('virtual_meeting', ...arguments)"
        >
          <template #filterButton>
            <button
              v-if="selectedComponent === 'all'"
              type="button"
              class="view-all"
              @click="preSelectedComponent = 'virtual_meeting'; applyFilters()">
              More
            </button>
          </template>
        </EventListing>
      </div>

      <div class="videos-wrapper" v-if="showComponent.gc_video">
        <VideoListing
          v-if="selectedComponent === 'gc_video' || selectedComponent === 'all'"
          :title="config.components.gc_video.title"
          :category="category.id"
          :viewAll="false"
          :sort="sortData('node')"
          :limit="itemsLimit"
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

      <div class="blogs-wrapper" v-if="showComponent.vy_blog_post">
        <BlogListing
          v-if="selectedComponent === 'vy_blog_post' || selectedComponent === 'all'"
          :title="config.components.vy_blog_post.title"
          :category="category.id"
          :viewAll="false"
          :sort="sortData('node')"
          :limit="itemsLimit"
          @listing-not-empty="listingIsNotEmpty('vy_blog_post', ...arguments)"
          class="my-40-20"
        >
          <template #filterButton>
            <button
              v-if="selectedComponent === 'all'"
              type="button"
              class="view-all"
              @click="preSelectedComponent = 'vy_blog_post'; applyFilters()">
              More
            </button>
          </template>
        </BlogListing>
      </div>
    </template>
  </div>
</template>

<script>
import client from '@/client';
import Spinner from '@/components/Spinner.vue';
import AddToFavorite from '@/components/AddToFavorite.vue';
import VideoListing from '@/components/video/VideoListing.vue';
import BlogListing from '@/components/blog/BlogListing.vue';
import EventListing from '@/components/event/EventListing.vue';
import { JsonApiCombineMixin } from '@/mixins/JsonApiCombineMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';
import { SettingsMixin } from '@/mixins/SettingsMixin';

export default {
  name: 'CategoryPage',
  mixins: [JsonApiCombineMixin, SettingsMixin, FilterAndSortMixin],
  components: {
    AddToFavorite,
    Spinner,
    VideoListing,
    BlogListing,
    EventListing,
  },
  props: {
    cid: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      loading: true,
      error: false,
      category: null,
      itemsLimit: 8,
      DEFAULT_SORT: 'date_asc',
      showComponent: {
        gc_video: true,
        vy_blog_post: true,
        live_stream: true,
        virtual_meeting: true,
      },
      showLiveStreamViewAll: false,
      showVirtualMeetingViewAll: false,
      filterQueryByTypes: {
        node: {
          date_desc: { path: 'created', direction: 'DESC' },
          date_asc: { path: 'created', direction: 'ASC' },
          title_asc: { path: 'title', direction: 'ASC' },
          title_desc: { path: 'title', direction: 'DESC' },
        },
        eventinstance: {
          date_desc: { path: 'date.value', direction: 'DESC' },
          date_asc: { path: 'date.value', direction: 'ASC' },
          title_asc: { path: 'eventseries_id.title', direction: 'ASC' },
          title_desc: { path: 'eventseries_id.title', direction: 'DESC' },
        },
      },
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
      this.loading = true;
      client
        .get(`jsonapi/taxonomy_term/gc_category/${this.cid}`)
        .then((response) => {
          this.category = response.data.data;
          this.loading = false;
        })
        .catch((error) => {
          this.error = true;
          this.loading = false;
          console.error(error);
          throw error;
        });
    },
    sortData(type) {
      return this.filterQueryByTypes[type][this.selectedSort];
    },
    listingIsNotEmpty(component, notEmpty) {
      this.showComponent[component] = notEmpty;
    },
  },
  computed: {
    viewAllContentMode() {
      // Enable viewAllContentMode only when we filter by content.
      return this.selectedComponent !== 'all';
    },
  },
};
</script>
