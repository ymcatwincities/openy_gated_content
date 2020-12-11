<template>
  <div class="gated-content-favorites-page">
    <Modal v-if="showModal" @close="showModal = false">
      <template v-slot:header>
        <h3>Adjust</h3>
      </template>
      <template v-slot:body>
        <div class="filter">
          <h4>Content types</h4>
          <div class="form-check" v-for="option in contentTypeOptions" v-bind:key="option.value">
            <input
              type="radio"
              :id="option.value"
              :value="option.value"
              autocomplete="off"
              v-model="preSelectedComponent"
              :disabled="option.type && isFavoritesTypeEmpty(option.type, option.value)"
            >
            <label :for="option.value">{{ option.label }}</label>
          </div>
        </div>
        <div class="sort">
          <h4>Sort order</h4>
          <div class="form-check" v-for="option in filterOptions" v-bind:key="option.value">
            <input
              type="radio"
              :id="option.value"
              :value="option.value"
              autocomplete="off"
              v-model="preSelectedSort"
            >
            <label :for="option.value">{{ option.label }}</label>
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

    <div v-if="!favoritesListInitialized" class="text-center">
      <Spinner></Spinner>
    </div>

    <div class="components-wrapper" v-else>
      <div class="gated-container text-right">
        <button type="button" class="btn btn-light" @click="showModal = true">Adjust</button>
      </div>

      <div v-if="isNoFavoriteItems" class="gated-container text-center">
        <span>There no favorite content.</span>
      </div>

      <div v-if="!isFavoritesTypeEmpty('node', 'gc_video')
        && (selectedComponent === 'gc_video' || selectedComponent === 'all')">
        <VideoListing
          :title="config.components.gc_video.title"
          :favorites="true"
          :pagination="viewAllContentMode"
          :sort="sortData('node')"
          :limit="viewAllContentMode ? 0 : itemsLimit"
        />
        <div class="text-center" v-if="selectedComponent === 'all'">
          <button
            type="button"
            class="btn btn-light"
            @click="preSelectedComponent = 'gc_video'; applyFilters()">
            View all
          </button>
        </div>
      </div>

      <div v-if="!isFavoritesTypeEmpty('eventinstance', 'live_stream')
        && (selectedComponent === 'live_stream' || selectedComponent === 'all')">
        <EventListing
          :title="config.components.live_stream.title"
          :msg="'Live streams not found.'"
          :favorites="true"
          :sort="sortData('eventinstance')"
          :limit="viewAllContentMode ? 50 : itemsLimit"
        />
        <div class="text-center" v-if="selectedComponent === 'all'">
          <button
            type="button"
            class="btn btn-light"
            @click="preSelectedComponent = 'live_stream'; applyFilters()">
            View all
          </button>
        </div>
      </div>

      <div v-if="!isFavoritesTypeEmpty('eventinstance', 'virtual_meeting')
        && (selectedComponent === 'virtual_meeting' || selectedComponent === 'all')">
        <EventListing
          :title="config.components.virtual_meeting.title"
          :eventType="'virtual_meeting'"
          :msg="'Virtual Meetings not found.'"
          :favorites="true"
          :sort="sortData('eventinstance')"
          :limit="viewAllContentMode ? 50 : itemsLimit"
        />
        <div class="text-center" v-if="selectedComponent === 'all'">
          <button
            type="button"
            class="btn btn-light"
            @click="preSelectedComponent = 'virtual_meeting'; applyFilters()">
            View all
          </button>
        </div>
      </div>

      <div v-if="!isFavoritesTypeEmpty('node', 'vy_blog_post')
        && (selectedComponent === 'vy_blog_post' || selectedComponent === 'all')">
        <BlogListing
          :title="config.components.vy_blog_post.title"
          :favorites="true"
          :pagination="viewAllContentMode"
          :sort="sortData('node')"
          :limit="viewAllContentMode ? 0 : itemsLimit"
        />
        <div class="text-center" v-if="selectedComponent === 'all'">
          <button
            type="button"
            class="btn btn-light"
            @click="preSelectedComponent = 'vy_blog_post'; applyFilters()">
            View all
          </button>
        </div>
      </div>

      <div v-if="!isFavoritesTypeEmpty('taxonomy_term', 'gc_category')
        && (selectedComponent === 'gc_category' || selectedComponent === 'all')">
        <CategoriesListing
          :favorites="true"
          :sort="sortData('taxonomy_term')"
          :limit="viewAllContentMode ? 50 : itemsLimit"
        />
        <div class="text-center" v-if="selectedComponent === 'all'">
          <button
            type="button"
            class="btn btn-light"
            @click="preSelectedComponent = 'gc_category'; applyFilters()">
            View all
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from '@/components/Spinner.vue';
import Modal from '@/components/Modal.vue';
import BlogListing from '@/components/blog/BlogListing.vue';
import VideoListing from '@/components/video/VideoListing.vue';
import EventListing from '@/components/event/EventListing.vue';
import CategoriesListing from '@/views/CategoriesListing.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FavoritesMixin } from '@/mixins/FavoritesMixin';

export default {
  name: 'FavoritesPage',
  mixins: [SettingsMixin, FavoritesMixin],
  components: {
    Spinner,
    Modal,
    BlogListing,
    VideoListing,
    EventListing,
    CategoriesListing,
  },
  data() {
    return {
      showModal: false,
      itemsLimit: 3,
      selectedComponent: 'all',
      preSelectedComponent: 'all',
      selectedSort: 'date_desc',
      preSelectedSort: 'date_desc',
      contentTypeOptions: [
        { value: 'all', label: 'Show All' },
        { value: 'gc_video', type: 'node', label: 'Video' },
        { value: 'live_stream', type: 'eventinstance', label: 'Live stream' },
        { value: 'virtual_meeting', type: 'eventinstance', label: 'Virtual meeting' },
        { value: 'vy_blog_post', type: 'node', label: 'Blog' },
        { value: 'gc_category', type: 'taxonomy_term', label: 'Categories' },
      ],
      filterOptions: [
        { value: 'date_desc', label: 'By date (New-Old)' },
        { value: 'date_asc', label: 'By date (Old-New)' },
        { value: 'title_asc', label: 'By title (A-Z)' },
        { value: 'title_desc', label: 'By title (Z-A)' },
      ],
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
        taxonomy_term: {
          date_desc: { path: 'changed', direction: 'DESC' },
          date_asc: { path: 'changed', direction: 'ASC' },
          title_asc: { path: 'name', direction: 'ASC' },
          title_desc: { path: 'name', direction: 'DESC' },
        },
      },
    };
  },
  computed: {
    favoritesList() {
      return this.$store.getters.getFavoritesList;
    },
    favoritesListInitialized() {
      let init = false;
      const list = this.favoritesList;
      Object.keys(list).forEach((key) => {
        if (typeof list[key] !== 'undefined') {
          init = true;
        }
      });
      return init;
    },
    viewAllContentMode() {
      // Enable viewAllContentMode only when we filter by content.
      return this.selectedComponent !== 'all';
    },
    isNoFavoriteItems() {
      const { favoritesList } = this;
      const filtered = this.contentTypeOptions.filter((item) => {
        if (item.value === 'all') {
          return false;
        }
        return favoritesList[item.type][item.value].length !== 0;
      });

      return filtered.length === 0;
    },
  },
  mounted() {
    if (this.$route.query.type) {
      this.selectedComponent = this.$route.query.type;
      this.preSelectedComponent = this.$route.query.type;
    }
    if (this.$route.query.sort) {
      this.selectedSort = this.$route.query.sort;
      this.preSelectedSort = this.$route.query.sort;
    }
  },
  watch: {
    favoritesList: {
      handler() {
        this.$forceUpdate();
      },
      deep: true,
    },
  },
  methods: {
    applyFilters() {
      this.selectedComponent = this.preSelectedComponent;
      this.selectedSort = this.preSelectedSort;
      const query = {
        ...this.$route.query,
        type: this.selectedComponent,
        sort: this.selectedSort,
      };
      if (Object.entries(this.$route.query).toString() !== Object.entries(query).toString()) {
        this.$router.push({ query });
      }
      this.showModal = false;
    },
    sortData(type) {
      return this.filterQueryByTypes[type][this.selectedSort];
    },
  },
};
</script>
