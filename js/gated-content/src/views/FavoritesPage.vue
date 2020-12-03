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
<!--      TODO: show message if no liked items for selected type else - show components-->
      <VideoListing
        v-if="selectedComponent === 'gc_video' || selectedComponent === 'all'"
        :title="config.components.gc_video.title"
        :favorites="true"
        :pagination="true"
        :sort="sortData('node')"
      />
      <EventListing
        v-if="selectedComponent === 'live_stream' || selectedComponent === 'all'"
        :title="config.components.live_stream.title"
        :msg="'Live streams not found.'"
        :favorites="true"
        :pagination="true"
        :sort="sortData('eventinstance')"
      />
      <EventListing
        v-if="selectedComponent === 'virtual_meeting' || selectedComponent === 'all'"
        :title="config.components.virtual_meeting.title"
        :eventType="'virtual_meeting'"
        :msg="'Virtual Meetings not found.'"
        :favorites="true"
        :pagination="true"
        :sort="sortData('eventinstance')"
      />
      <BlogListing
        v-if="selectedComponent === 'vy_blog_post' || selectedComponent === 'all'"
        :title="config.components.vy_blog_post.title"
        :favorites="true"
        :pagination="true"
        :sort="sortData('node')"
      />
      <CategoriesListing
        v-if="selectedComponent === 'gc_category' || selectedComponent === 'all'"
        :favorites="true"
        :sort="sortData('taxonomy_term')"
      />
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

export default {
  name: 'FavoritesPage',
  mixins: [SettingsMixin],
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
      selectedComponent: 'all',
      preSelectedComponent: 'all',
      selectedSort: 'date_desc',
      preSelectedSort: 'date_desc',
      contentTypeOptions: [
        { value: 'all', label: 'Show All' },
        { value: 'gc_video', label: 'Video' },
        { value: 'live_stream', label: 'Live stream' },
        { value: 'virtual_meeting', label: 'Virtual meeting' },
        { value: 'vy_blog_post', label: 'Blog' },
        { value: 'gc_category', label: 'Categories' },
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
      this.showModal = false;
    },
    sortData(type) {
      return this.filterQueryByTypes[type][this.selectedSort];
    },
  },
};
</script>
