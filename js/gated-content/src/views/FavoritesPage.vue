<template>
  <div class="gated-content-favorites-page">
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
                autocomplete="off"
                v-model="preSelectedComponent"
                :disabled="option.type && isFavoritesTypeEmpty(option.type, option.value)"
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

    <div v-if="!favoritesListInitialized" class="text-center">
      <Spinner></Spinner>
    </div>

    <div class="components-wrapper" v-else>
      <div class="gated-containerV2 my-40-20 px--20-10 title-wrapper">
        <div class="title cachet-book-32-28 text-gray">Favorites</div>
        <button type="button"
                class="adjust-button" @click="showModal = true">Filter</button>
      </div>

      <div v-if="isNoFavoriteItems" class="gated-container text-center">
        <span>There is no favorite content.</span>
      </div>

      <PersonalTrainingListing
        :favorites="true"
        :limit="viewAllContentMode ? 0 : itemsLimit"
        :sort="sortData('personal_training')"
        v-if="!isFavoritesTypeEmpty('personal_training', 'personal_training')
          && config.personal_training_enabled
          && (selectedComponent === 'personal_training' || selectedComponent === 'all')">
        <template #filterButton>
          <button
            v-if="selectedComponent === 'all'"
            type="button"
            class="view-all"
            @click="preSelectedComponent = 'personal_training'; applyFilters()">
            More
          </button>
        </template>
      </PersonalTrainingListing>

      <div v-for="component in componentsOrder" :key="component">
        <div v-if="!isFavoritesTypeEmpty('node', 'gc_video')
          && showOnCurrentIteration('gc_video', component)
          && (selectedComponent === 'gc_video' || selectedComponent === 'all')">
          <VideoListing
            :title="config.components.gc_video.title"
            :favorites="true"
            :pagination="viewAllContentMode"
            :sort="sortData('node', 'gc_video')"
            :limit="viewAllContentMode ? 0 : itemsLimit"
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

        <div v-if="!isFavoritesTypeEmpty('eventinstance', 'live_stream')
          && showOnCurrentIteration('live_stream', component)
          && (selectedComponent === 'live_stream' || selectedComponent === 'all')">
          <EventListing
            :title="config.components.live_stream.title"
            :favorites="true"
            :sort="sortData('eventinstance', 'live_stream')"
            :limit="viewAllContentMode ? 50 : itemsLimit"
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

        <div v-if="!isFavoritesTypeEmpty('eventinstance', 'virtual_meeting')
          && showOnCurrentIteration('virtual_meeting', component)
          && (selectedComponent === 'virtual_meeting' || selectedComponent === 'all')">
          <EventListing
            :title="config.components.virtual_meeting.title"
            :eventType="'virtual_meeting'"
            :favorites="true"
            :sort="sortData('eventinstance', 'virtual_meeting')"
            :limit="viewAllContentMode ? 50 : itemsLimit"
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

        <div v-if="!isFavoritesTypeEmpty('node', 'vy_blog_post')
          && showOnCurrentIteration('vy_blog_post', component)
          && (selectedComponent === 'vy_blog_post' || selectedComponent === 'all')">
          <BlogListing
            :title="config.components.vy_blog_post.title"
            :favorites="true"
            :pagination="viewAllContentMode"
            :sort="sortData('node', 'vy_blog_post')"
            :limit="viewAllContentMode ? 0 : itemsLimit"
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
      </div>

      <div v-if="!isFavoritesTypeEmpty('taxonomy_term', 'gc_category')
        && (selectedComponent === 'gc_category' || selectedComponent === 'all')">
        <CategoriesListing
          :favorites="true"
          :type="'all'"
          :sort="sortData('taxonomy_term')"
          :limit="viewAllContentMode ? 50 : itemsLimit"
        >
          <template #filterButton>
            <button
              v-if="selectedComponent === 'all'"
              type="button"
              class="view-all"
              @click="preSelectedComponent = 'gc_category'; applyFilters()">
              More
            </button>
          </template>
        </CategoriesListing>
      </div>
    </div>
  </div>
</template>

<script>
import Spinner from '@/components/Spinner.vue';
import BlogListing from '@/components/blog/BlogListing.vue';
import VideoListing from '@/components/video/VideoListing.vue';
import EventListing from '@/components/event/EventListing.vue';
import PersonalTrainingListing from '@/components/personal-training/PersonalTrainingListing.vue';
import CategoriesListing from '@/components/category/CategoriesListing.vue';
import { SettingsMixin } from '@/mixins/SettingsMixin';
import { FavoritesMixin } from '@/mixins/FavoritesMixin';
import { FilterAndSortMixin } from '@/mixins/FilterAndSortMixin';

export default {
  name: 'FavoritesPage',
  mixins: [SettingsMixin, FavoritesMixin, FilterAndSortMixin],
  components: {
    Spinner,
    BlogListing,
    VideoListing,
    EventListing,
    PersonalTrainingListing,
    CategoriesListing,
  },
  data() {
    return {
      itemsLimit: 8,
      DEFAULT_SORT: 'date_asc',
      contentTypeOptions: [
        { value: 'all', label: 'Show All' },
        { value: 'gc_video', type: 'node', label: 'Video' },
        { value: 'live_stream', type: 'eventinstance', label: 'Live stream' },
        { value: 'virtual_meeting', type: 'eventinstance', label: 'Virtual meeting' },
        { value: 'vy_blog_post', type: 'node', label: 'Blog' },
        { value: 'gc_category', type: 'taxonomy_term', label: 'Categories' },
      ],
    };
  },
  computed: {
    favoritesList() {
      return this.$store.getters.getFavoritesList;
    },
    favoritesListInitialized() {
      const list = this.favoritesList;
      // Store initialized when at least one of the keys not undefined.
      return Object.keys(list).some((key) => typeof list[key] !== 'undefined');
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
  watch: {
    favoritesList: {
      handler() {
        this.$forceUpdate();
      },
      deep: true,
    },
  },
  mounted() {
    if (this.config.personal_training_enabled) {
      this.contentTypeOptions.splice(1, 0, { value: 'personal_training', type: 'personal_training', label: 'Personal training' });
    }
  },
};
</script>
